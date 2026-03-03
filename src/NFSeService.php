<?php

namespace NFSePHP;

use NFePHP\Common\Certificate as NFeCertificate;
use NFePHP\Common\Signer;
use NFSePHP\DTO\DpsResponse;
use NFSePHP\DTO\EnderecoDTO;
use NFSePHP\DTO\EventoCancelamentoDTO;
use NFSePHP\DTO\EventoCancelamentoResponseDTO;
use NFSePHP\DTO\EventoResponse;
use NFSePHP\DTO\EventosConsultaDTO;
use NFSePHP\DTO\EventosResponse;
use NFSePHP\DTO\InfDpsDTO;
use NFSePHP\DTO\PrestadorDTO;
use NFSePHP\DTO\SefinNacionalResponse;
use NFSePHP\DTO\ServicoDTO;
use NFSePHP\DTO\TomadorDTO;
use NFSePHP\DTO\ValoresServicoDTO;
use NFSePHP\Exception\InvalidXSDException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NFSeService
{
    private const ENDPOINT_HOMOLOG = 'https://sefin.producaorestrita.nfse.gov.br/SefinNacional/nfse';
    private const ENDPOINT_PROD = 'https://sefin.nfse.gov.br/SefinNacional/nfse';
    private Certificate $certificate;

    /**
     * Optional override. When null, endpoint is resolved from InfDpsDTO->tpAmb (1 = prod, 2 = homolog).
     */
    private ?string $endpointUrlOverride;

    public function __construct(
        Certificate $certificate,
        private ?HttpClientInterface $httpClient = null,
        private ?XmlEncoder $encoder = null,
        ?string $endpointUrl = null,
        private ?ValidatorInterface $validator = null,
        private readonly ?LoggerInterface $logger = null,
        private readonly bool $debug = false,
    ) {
        $this->certificate = $certificate;
        $this->endpointUrlOverride = $endpointUrl;
    }

    /**
     * Create, sign and send DPS to NFS-e Nacional.
     *
     * @return DpsResponse HTTP status, raw body and parsed SefinNacionalResponse when body is JSON
     *
     * @throws ValidationFailedException
     * @throws InvalidXSDException
     */
    public function createNFSe(InfDpsDTO $infDpsDTO, bool $validateDTOs = true): DpsResponse
    {
        if ($validateDTOs) {
            $this->validate($infDpsDTO);
        }

        $xml = $this->getDpsXml($infDpsDTO);

        $signedXml = $this->signXml($xml);

        // Validate signed XML against XSD schema (Signature is required in final XML)
        try {
            $this->validateXmlAgainstXsd($signedXml);
        } catch (InvalidXSDException $e) {
            $this->logDebug('XSD Validation failed', ['errors' => $e->getErrors()]);
            // In non-debug mode, continue even if XSD validation fails.
        }

        // Gzip compress and base64 encode the signed XML
        $gzippedXml = gzencode($signedXml, 9);
        $base64GzippedXml = base64_encode($gzippedXml);

        // Get client certificate and private key file paths for HTTPS authentication
        $pemPaths = $this->certificate->getPemFilePaths();

        $endpointUrl = $this->resolveEndpointUrl($infDpsDTO->tpAmb);
        $response = $this->getHttpClient()->request('POST', $endpointUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'dpsXmlGZipB64' => $base64GzippedXml,
            ]),
            'local_cert' => $pemPaths['cert'],
            'local_pk' => $pemPaths['key'],
        ]);

        $statusCode = $response->getStatusCode();
        $rawBody = $response->getContent(false);

        $parsed = null;
        try {
            $parsed = SefinNacionalResponse::fromJson($rawBody);
        } catch (\JsonException) {
            // Body is not JSON (e.g. HTML error page)
        }

        return new DpsResponse(
            statusCode: $statusCode,
            rawBody: $rawBody,
            response: $parsed,
        );
    }

    /**
     * Register Evento de Cancelamento de NFS-e (e101101) for a given NFSe.
     *
     * @throws InvalidXSDException
     */
    public function cancelNFSe(EventoCancelamentoDTO $evento): EventoResponse
    {
        $xml = $this->getCancelamentoEventoXml($evento);

        $signedXml = $this->signXml($xml, 'infPedReg');

        try {
            $this->validateEventoXmlAgainstXsd($signedXml);
        } catch (InvalidXSDException $e) {
            $this->logDebug('XSD Evento Validation failed', ['errors' => $e->getErrors()]);
        }

        $gzippedXml = gzencode($signedXml, 1);
        $base64GzippedXml = base64_encode($gzippedXml);

        $pemPaths = $this->certificate->getPemFilePaths();

        $endpointUrl = $this->resolveEndpointUrl($evento->tpAmb).'/'.$evento->chNFSe.'/eventos';

        $this->logDebug('Sending Evento de Cancelamento', ['endpoint' => $endpointUrl]);

        $response = $this->getHttpClient()->request('POST', $endpointUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'pedidoRegistroEventoXmlGZipB64' => $base64GzippedXml,
            ]),
            'local_cert' => $pemPaths['cert'],
            'local_pk' => $pemPaths['key'],
        ]);

        $statusCode = $response->getStatusCode();
        $rawBody = $response->getContent(false);
        $parsed = null;

        try {
            $parsed = EventoCancelamentoResponseDTO::fromJson($rawBody);
        } catch (\JsonException) {
            // Body is not JSON (e.g. HTML error page)
        }

        return new EventoResponse(
            statusCode: $statusCode,
            rawBody: $rawBody,
            response: $parsed,
        );
    }

    /**
     * GET eventos for a given NFSe: nfse/{chaveAcesso}/eventos/{tipoEvento}/{numSeqEvento}.
     *
     * @param string $chaveAcesso  NFSe access key (e.g. from SefinNacionalResponse->chaveAcesso)
     * @param string $tipoEvento   Event type code (e.g. 101101 for cancelamento)
     * @param string $numSeqEvento Sequence number (e.g. 1)
     * @param string $tpAmb        Environment: '1' = Produção, '2' = Homologação
     */
    public function getEvents(
        string $chaveAcesso,
        string $tipoEvento,
        string $numSeqEvento,
        string $tpAmb = '2',
    ): EventosResponse {
        $pemPaths = $this->certificate->getPemFilePaths();

        $endpointUrl = $this->resolveEndpointUrl($tpAmb).'/'.$chaveAcesso.'/eventos/'.$tipoEvento.'/'.$numSeqEvento;

        $this->logDebug('GET eventos', ['endpoint' => $endpointUrl]);

        $response = $this->getHttpClient()->request('GET', $endpointUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'local_cert' => $pemPaths['cert'],
            'local_pk' => $pemPaths['key'],
        ]);

        $statusCode = $response->getStatusCode();
        $rawBody = $response->getContent(false);

        $parsed = null;
        try {
            $decoded = json_decode($rawBody, true, 512, \JSON_THROW_ON_ERROR);
            if (\is_array($decoded)) {
                $parsed = EventosConsultaDTO::fromArray($decoded);
            }
        } catch (\JsonException) {
            // Body is not JSON
        }

        return new EventosResponse(
            statusCode: $statusCode,
            rawBody: $rawBody,
            response: $parsed,
        );
    }

    /**
     * Log a debug message when a logger is set or debug flag is true (fallback: stderr).
     * Use a PSR-3 logger in production; use $debug = true for ad-hoc stderr output.
     */
    private function logDebug(string $message, array $context = []): void
    {
        if (null !== $this->logger) {
            $this->logger->debug($message, $context);

            return;
        }

        if (!$this->debug) {
            return;
        }

        fwrite(STDERR, '['.__CLASS__.'] '.$message.PHP_EOL);
        if (isset($context['errors']) && is_array($context['errors'])) {
            foreach ($context['errors'] as $error) {
                fwrite(STDERR, '  - '.(is_string($error) ? $error : json_encode($error, JSON_UNESCAPED_UNICODE)).PHP_EOL);
            }
        }
        if (isset($context['endpoint'])) {
            fwrite(STDERR, '  endpoint: '.$context['endpoint'].PHP_EOL);
        }
        if (isset($context['xml'])) {
            fwrite(STDERR, $context['xml'].PHP_EOL);
        }
    }

    /**
     * Validate InfDpsDTO using Symfony Validator (including nested DTOs).
     *
     * @throws ValidationFailedException when validation fails
     */
    private function validate(InfDpsDTO $infDpsDTO): void
    {
        $violations = $this->getValidator()->validate($infDpsDTO);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($infDpsDTO, $violations);
        }
    }

    private function getValidator(): ValidatorInterface
    {
        if (null === $this->validator) {
            $this->validator = Validation::createValidatorBuilder()
                ->enableAttributeMapping()
                ->getValidator();
        }

        return $this->validator;
    }

    /**
     * Resolve endpoint from tpAmb (1 = Produção, 2 = Homologação) or override.
     */
    private function resolveEndpointUrl(string $tpAmb): string
    {
        if (null !== $this->endpointUrlOverride && '' !== $this->endpointUrlOverride) {
            return $this->endpointUrlOverride;
        }

        return '1' === $tpAmb ? self::ENDPOINT_PROD : self::ENDPOINT_HOMOLOG;
    }

    private function getDpsXml(InfDpsDTO $infDpsDTO): string
    {
        // Build DPS structure from DPS DTO (according to DPS_v1.01.xsd)
        // Root element is <DPS> with @versao attribute and infDPS child
        $dps = [
            'DPS' => [
                '@xmlns' => 'http://www.sped.fazenda.gov.br/nfse',
                '@versao' => $infDpsDTO->versao ?? '1.01',
                'infDPS' => $this->buildInfDPS($infDpsDTO),
            ],
        ];

        $xml = $this->getEncoder()->encode($dps, 'xml', [
            'xml_root_node_name' => 'rootnode',
            'remove_empty_tags' => true,
            'xml_format_output' => true,
            'xml_encoding' => 'UTF-8',
        ]);

        // Clean up encode tag added by encoder
        /* $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml); */
        $xml = str_replace('<rootnode>', '', $xml);
        $xml = str_replace('</rootnode>', '', $xml);
        $xml = trim($xml);

        $this->logDebug('DPS XML (signed)', ['xml' => $xml]);

        return $xml;
    }

    /**
     * Build XML for Evento de Cancelamento (e101101).
     */
    private function getCancelamentoEventoXml(EventoCancelamentoDTO $evento): string
    {
        $tipoEvento = '101101';
        $idPedReg = 'PRE'.$evento->chNFSe.$tipoEvento; // TSIdPedRegEvt (1.00): PRE + 56 dígitos (chave 50 + tipo 6), maxLength 59 — sem nPedRegEvento no Id
        $autorKey = null !== $evento->cnpjAutor ? 'CNPJAutor' : 'CPFAutor';
        $autorVal = $evento->cnpjAutor ?? $evento->cpfAutor;

        $eventoArr = [
            'pedRegEvento' => [
                '@xmlns' => 'http://www.sped.fazenda.gov.br/nfse',
                '@versao' => '1.01',
                'infPedReg' => [
                    '@Id' => $idPedReg,
                    'tpAmb' => $evento->tpAmb,
                    'verAplic' => $evento->verAplic,
                    // 'nSeqEvento' => 1,
                    'dhEvento' => $evento->dhEvento,
                    $autorKey => $autorVal,
                    'chNFSe' => $evento->chNFSe,
                    'e101101' => [
                        'xDesc' => 'Cancelamento de NFS-e',
                        'cMotivo' => $evento->cMotivo,
                        'xMotivo' => $evento->xMotivo,
                    ],
                ],
            ],
        ];

        $xml = $this->getEncoder()->encode($eventoArr, 'xml', [
            'xml_root_node_name' => 'rootnode',
            'remove_empty_tags' => true,
            'xml_format_output' => true,
            'xml_encoding' => 'UTF-8',
        ]);

        $xml = str_replace('<rootnode>', '', $xml);
        $xml = str_replace('</rootnode>', '', $xml);
        $xml = trim($xml);

        $this->logDebug('Evento cancelamento XML', ['xml' => $xml]);

        return $xml;
    }

    private function buildValores(ValoresServicoDTO $valores): array
    {
        return [
            // 1
            'vServPrest' => [
                'vServ' => $valores->vServ,
            ],

            // 2. @TODO: vDescCondIncond (optional)

            // 3. @TODO: vDedRed (optional) - contains choice: pDR, vDR, or documentos

            // 4 - trib
            'trib' => [
                'tribMun' => [
                    'tribISSQN' => $valores->tribISSQN, // Default: Operação tributável
                    'tpRetISSQN' => $valores->tpRetISSQN, // Default: Não Retido (1 - Não Retido; 2 - Retido pelo Tomador; 3 - Retido pelo Intermediario)
                ],
                'totTrib' => [
                    'vTotTrib' => [
                        'vTotTribFed' => $valores->vTotTribFed, // Tributos federais
                        'vTotTribEst' => $valores->vTotTribEst, // Tributos estaduais
                        'vTotTribMun' => $valores->vTotTribMun, // Tributos municipais
                    ],
                ],
            ],

            // 'pAliqAplic' => $valores->pAliqAplic,
            // 'vBC' => $valores->vBC,
            // 'vISSQN' => $valores->vISSQN,
            // 'vCalcDR' => $valores->vCalcDR,
            // 'tpBM' => $valores->tpBM,
            // 'vCalcBM' => $valores->vCalcBM,
            // 'vTotalRet' => $valores->vTotalRet,
            // 'vLiq' => $valores->vLiq,
        ];
    }

    /**
     * Generate DPS ID from DTO values according to specification:
     * "DPS" + Cód.Mun(7) + Tipo Inscrição(1) + Inscrição(14) + Série(5) + Número(15)
     */
    private function generateDpsId(InfDpsDTO $infDps): string
    {
        // Get cLocEmi (7 digits) - Código IBGE do município
        $codMun = str_pad($infDps->cLocEmi, 7, '0', STR_PAD_LEFT);

        // Get prestador CNPJ/CPF to determine tipo inscrição and inscrição federal
        $prest = $infDps->prest;

        if (null !== $prest->cnpj && '' !== $prest->cnpj) {
            $tipoInscricao = '2'; // CNPJ
            $inscricaoFederal = str_pad(preg_replace('/\D/', '', $prest->cnpj), 14, '0', STR_PAD_LEFT);
        } elseif (null !== $prest->cpf && '' !== $prest->cpf) {
            $tipoInscricao = '1'; // CPF
            $inscricaoFederal = str_pad(preg_replace('/\D/', '', $prest->cpf), 14, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('Prestador must have either CNPJ or CPF for DPS ID generation');
        }

        // Get serie (5 digits, zero-padded)
        $serie = str_pad($infDps->serie, 5, '0', STR_PAD_LEFT);

        // Get nDPS (15 digits, zero-padded)
        $numeroDPS = str_pad($infDps->nDPS, 15, '0', STR_PAD_LEFT);

        // Build DPS ID: "DPS" + Cód.Mun(7) + Tipo Inscrição(1) + Inscrição(14) + Série(5) + Número(15)
        return 'DPS'.$codMun.$tipoInscricao.$inscricaoFederal.$serie.$numeroDPS;
    }

    private function buildInfDPS(InfDpsDTO $infDps): array
    {
        // Generate DPS ID from actual DTO values to ensure it matches the XML fields
        $dpsId = $this->generateDpsId($infDps);

        // Build in the exact order required by XSD schema (TCInfDPS)
        $result = [
            '@Id' => $dpsId, // Id is an attribute, generated from actual DTO values
            'tpAmb' => $infDps->tpAmb,
            'dhEmi' => $infDps->dhEmi,
            'verAplic' => $infDps->verAplic,
            'serie' => $infDps->serie,
            'nDPS' => $infDps->nDPS,
            'dCompet' => $infDps->dCompet,
            'tpEmit' => $infDps->tpEmit,
        ];

        // @TODO: Optional: cMotivoEmisTI (before cLocEmi)
        if (null !== $infDps->cMotivoEmisTI) {
            $result['cMotivoEmisTI'] = $infDps->cMotivoEmisTI;
        }

        // @TODO: Optional: chNFSeRej (before cLocEmi)
        if (null !== $infDps->chNFSeRej) {
            $result['chNFSeRej'] = $infDps->chNFSeRej;
        }

        $result['cLocEmi'] = $infDps->cLocEmi;

        // @TODO: Optional: subst (after cLocEmi, before prest)
        // if (null !== $infDps->subst) {
        //     $result['subst'] = $this->buildSubstituicao($infDps->subst);
        // }

        $result['prest'] = $this->buildPrestador($infDps->prest);

        // Optional: toma (after prest, before serv)
        if (null !== $infDps->toma) {
            $result['toma'] = $this->buildTomador($infDps->toma);
        }

        // @TODO: Optional: interm (after toma, before serv)
        // if (null !== $infDps->interm) {
        //     $result['interm'] = $this->buildIntermediario($infDps->interm);
        // }

        $result['serv'] = $this->buildServico($infDps->serv);

        $result['valores'] = $this->buildValores($infDps->valores);

        // @TODO: Optional: IBSCBS (after valores)
        // if (null !== $infDps->ibscbs) {
        //     $result['IBSCBS'] = $this->buildIbsCbsInfo($infDps->ibscbs);
        // }

        return $result;
    }

    private function buildPrestador(PrestadorDTO $prest): array
    {
        // CNPJ/CPF/NIF/cNaoNIF (choice - one must be present)
        $result['CNPJ'] = $prest->cnpj;
        $result['CPF'] = $prest->cpf;
        $result['NIF'] = $prest->nif;
        $result['cNaoNIF'] = $prest->cNaoNIF;

        // CAEPF (optional)
        $result['CAEPF'] = $prest->caepf;

        // IM (optional)
        $result['IM'] = $prest->im;

        // xNome (optional)
        $result['xNome'] = $prest->xNome;

        // end (optional)
        if (null !== $prest->end) {
            $result['end'] = $this->buildEndereco($prest->end);
        }

        // fone (optional)
        $result['fone'] = $prest->fone;

        // email (optional)
        $result['email'] = $prest->email;

        // regTrib (required) - must come last
        $result['regTrib'] = [
            'opSimpNac' => $prest->regTrib->opSimpNac,
            'regEspTrib' => $prest->regTrib->regEspTrib,
            'regApTribSN' => $prest->regTrib->regApTribSN,
        ];

        return $result;
    }

    private function buildTomador(TomadorDTO $toma): array
    {
        // Build in the exact order required by XSD schema (TCInfoPessoa)
        // CNPJ/CPF/NIF/cNaoNIF (choice - one must be present)
        $result['CNPJ'] = $toma->cnpj;
        $result['CPF'] = $toma->cpf;
        $result['NIF'] = $toma->nif;
        $result['cNaoNIF'] = $toma->cNaoNIF;

        // CAEPF (optional)
        $result['CAEPF'] = $toma->caepf;

        // IM (optional)
        $result['IM'] = $toma->im;

        // xNome (required)
        $result['xNome'] = $toma->xNome;

        // end (optional)
        if (null !== $toma->end) {
            $result['end'] = $this->buildEndereco($toma->end);
        }

        // fone (optional)
        if (null !== $toma->fone) {
            $result['fone'] = $toma->fone;
        }

        // email (optional)
        if (null !== $toma->email) {
            $result['email'] = $toma->email;
        }

        return $result;
    }

    private function buildEndereco(EnderecoDTO $end): array
    {
        return [
            'endNac' => [
                'cMun' => $end->endNac->cMun,
                'CEP' => $end->endNac->CEP,
            ],
            'xLgr' => $end->xLgr,
            'nro' => $end->nro,
            'xCpl' => $end->xCpl,
            'xBairro' => $end->xBairro,
        ];
    }

    private function buildServico(ServicoDTO $serv): array
    {
        return [
            'locPrest' => [
                'cLocPrestacao' => $serv->locPrest->cLocPrestacao,
                // 'cPaisPrestacao' => '105',
            ],
            'cServ' => [
                'cTribNac' => $serv->cServ->cTribNac,
                'cTribMun' => $serv->cServ->cTribMun,
                'xDescServ' => $serv->cServ->xDescServ,
                'cNBS' => $serv->cServ->cNBS,
                'cIntContrib' => $serv->cServ->cIntContrib,
            ],
        ];
    }

    private function getEncoder(): XmlEncoder
    {
        if (null === $this->encoder) {
            $this->encoder = new XmlEncoder();
        }

        return $this->encoder;
    }

    private function getHttpClient(): HttpClientInterface
    {
        if (null === $this->httpClient) {
            $this->httpClient = HttpClient::create();
        }

        return $this->httpClient;
    }

    /**
     * @throws InvalidXSDException
     */
    private function signXml(string $xml, string $tagname = 'infDPS', string $mark = 'Id'): string
    {
        $algorithm = OPENSSL_ALGO_SHA256; // algoritmo de encriptação
        $canonical = [true, false, null, null]; // veja função C14n do PHP
        $rootname = ''; // node onde a assinatura será incluída

        // Get PFX content and password from Certificate instance
        $pfx = $this->certificate->getPfxContent();
        $pfxPassword = $this->certificate->getPfxPassword();

        // Use NFePHP Certificate class for signing
        $nfeCertificate = NFeCertificate::readPfx($pfx, $pfxPassword);

        $xmlAssinado = Signer::sign(
            $nfeCertificate,
            $xml,
            $tagname,
            $mark,
            $algorithm,
            $canonical,
            $rootname
        );

        // Remove any existing XML declaration
        /* $xmlAssinado = preg_replace('/<\?xml[^>]*\?>/', '', $xmlAssinado); */
        // $xmlAssinado = trim($xmlAssinado);

        // Ensure the content is UTF-8 encoded
        if (!mb_check_encoding($xmlAssinado, 'UTF-8')) {
            $xmlAssinado = mb_convert_encoding($xmlAssinado, 'UTF-8', mb_detect_encoding($xmlAssinado));
        }

        // Add UTF-8 XML declaration at the beginning
        $xmlAssinado = '<?xml version="1.0" encoding="UTF-8"?>'."\n".$xmlAssinado;

        $this->logDebug('Signed XML', ['xml' => $xmlAssinado]);

        return $xmlAssinado;
    }

    /**
     * @throws InvalidXSDException
     */
    private function validateEventoXmlAgainstXsd(string $xml): void
    {
        $xsdPath = __DIR__.'/../xsd/1.01/pedRegEvento_v1.01.xsd';
        $this->doXmlXsdValidation(
            $xml,
            $xsdPath,
            'Invalid Evento XML',
            'Evento XSD validation failed',
        );
    }

    /**
     * @throws InvalidXSDException
     */
    private function validateXmlAgainstXsd(string $xml): void
    {
        $xsdPath = __DIR__.'/../xsd/1.01/DPS_v1.01.xsd';
        $this->doXmlXsdValidation(
            $xml,
            $xsdPath,
            'Invalid XML',
            'XSD validation failed',
        );
    }

    /**
     * @throws InvalidXSDException
     */
    private function doXmlXsdValidation(string $xml, string $xsdPath, string $invalidPrefix, string $xsdPrefix): void
    {
        if (!file_exists($xsdPath)) {
            throw new InvalidXSDException("XSD schema file not found: $xsdPath");
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;

        libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);

        if (!$loaded) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $errorMessages = array_map(static fn ($error) => trim($error->message), $errors);

            throw new InvalidXSDException($invalidPrefix.': '.implode('; ', $errorMessages), $errorMessages);
        }

        libxml_use_internal_errors(true);
        $valid = @$dom->schemaValidate($xsdPath);

        if (!$valid) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $errorMessages = array_map(static function ($error) {
                $level = LIBXML_ERR_WARNING === $error->level ? 'Warning' : (LIBXML_ERR_ERROR === $error->level ? 'Error' : 'Fatal');

                return sprintf('[%s] Line %d: %s', $level, $error->line, trim($error->message));
            }, $errors);

            throw new InvalidXSDException($xsdPrefix.': '.implode('; ', $errorMessages), $errorMessages);
        }

        libxml_use_internal_errors(false);
    }
}
