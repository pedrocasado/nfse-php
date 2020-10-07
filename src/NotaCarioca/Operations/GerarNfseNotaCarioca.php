<?php

namespace NFSePHP\NotaCarioca\Operations;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use NFSePHP\NotaCarioca\NotaCariocaOperationBase;

/**
 * Class to generate XML to the GerarNfse Web Service operation.
 */
class GerarNfseNotaCarioca extends NotaCariocaOperationBase
{
    public function __construct(string $env = 'dev', array $rps = [])
    {
        parent::__construct($env, $rps);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation(): string
    {
        return 'GerarNfse';
    }

    /**
     * {@inheritdoc}
     */
    public function formatSuccessResponse(string $responseXml): array
    {
        $resultArr = $this->getEncoder()->decode($responseXml, '');

        $responseArr['nfse'] = $resultArr['CompNfse']['Nfse']['InfNfse'];

        return $responseArr;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaStructure(): array
    {
        return [
            'InfRps' => [
                'IdentificacaoRps' => ['Numero', 'Serie', 'Tipo'],
                'DataEmissao',
                'NaturezaOperacao',
                'RegimeEspecialTributacao:?',
                'OptanteSimplesNacional',
                'IncentivadorCultural',
                'Status',
                'RpsSubstituido?' => ['Numero', 'Serie', 'Tipo'],
                'Servico' => [
                    'Valores' => [
                        'ValorServicos',
                        'ValorDeducoes?',
                        'ValorPis?',
                        'ValorCofins?',
                        'ValorInss?',
                        'ValorIr?',
                        'ValorCsll?',
                        'IssRetido',
                        'ValorIss?',
                        'OutrasRetencoes?',
                        'Aliquota?',
                        'DescontoIncondicionado?',
                        'DescontoCondicionado?',
                    ],
                    'ItemListaServico',
                    'CodigoTributacaoMunicipio',
                    'Discriminacao',
                    'CodigoMunicipio',
                ],
                'Tomador' => [
                    'IdentificacaoTomador?' => [
                        'CpfCnpj' => ['Cpf?', 'Cnpj?'],
                    ],
                    'RazaoSocial?',
                    'Endereco?' => ['Endereco?', 'Numero?', 'Complemento?', 'Bairro?', 'CodigoMunicipio?', 'Uf?', 'Cep?'],
                ],
                'Prestador' => ['Cnpj', 'InscricaoMunicipal?'],
                'IntermediarioServico?' => [
                    'CpfCnpj' => ['Cpf?', 'Cnpj?'],
                    'RazaoSocial',
                    'InscricaoMunicipal?',
                ],
                'ConstrucaoCivil?' => ['CodigoObra', 'Art'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function getEnvelopeXml(): string
    {
        $structure = $this->getSchemaStructure();

        $rps = [
            'InfRps' => [
                '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                '@Id' => $this->rps['IdentificacaoRps']['Numero'],
                'IdentificacaoRps' => $this->rps['IdentificacaoRps'],
                'DataEmissao' => $this->rps['DataEmissao'],
                'NaturezaOperacao' => $this->rps['NaturezaOperacao'],
                'RegimeEspecialTributacao' => isset($this->rps['RegimeEspecialTributacao']) ? $this->rps['RegimeEspecialTributacao'] : null,
                'OptanteSimplesNacional' => $this->rps['OptanteSimplesNacional'],
                'IncentivadorCultural' => $this->rps['IncentivadorCultural'],
                'Status' => $this->rps['Status'],
                'RpsSubstituido' => isset($this->rps['RpsSubstituido']) ? $this->rps['RpsSubstituido'] : null,
                // @TODO Set parameters one by one to remove the need to worry about order
                // Order Matters (Servico must be before Prestador)
                'Servico' => [
                    // Order Matters (Valores must be before ItemListaServico)
                    'Valores' => $this->rps['Servico']['Valores'],
                    'ItemListaServico' => $this->rps['Servico']['ItemListaServico'],
                    'CodigoTributacaoMunicipio' => $this->rps['Servico']['CodigoTributacaoMunicipio'],
                    'Discriminacao' => $this->rps['Servico']['Discriminacao'],
                    'CodigoMunicipio' => $this->rps['Servico']['CodigoMunicipio'],
                ],
                'Prestador' => $this->rps['Prestador'], // Order matters - Must be before Tomador
                'Tomador' => $this->rps['Tomador'],
                'IntermediarioServico' => isset($this->rps['IntermediarioServico']) ? $this->rps['IntermediarioServico'] : null,
                'ConstrucaoCivil' => isset($this->rps['ConstrucaoCivil']) ? $this->rps['ConstrucaoCivil'] : null,
            ],
        ];

        // Validate array based on structure
        try {
            $schema = Schema::parse($structure);
            $schema->validate($rps);
        } catch (ValidationException $ex) {
            throw new \Exception(__FILE__.':'.__LINE__.' - '.$ex->getMessage());
        }

        $xml = $this->getEncoder()->encode($rps, 'xml', ['xml_root_node_name' => 'Rps', 'remove_empty_tags' => true]);

        // clean up encode tag added by encoder
        $xml = str_replace('<?xml version="1.0"?>', '', $xml);

        $content = '<GerarNfseEnvio xmlns="http://notacarioca.rio.gov.br/WSNacional/XSD/1/nfse_pcrj_v01.xsd">'.$xml.'</GerarNfseEnvio>';

        // Envelope request
        $this->addEnvelope($content);

        // header('Content-type: text/xml');
        // print_r($content);
        // exit();

        return $content;
    }
}
