<?php

namespace NFSePHP\NotaCarioca;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use NFSePHP\XmlFactoryInterface;

/**
 * Class to generate XML to the ConsultarNfse Web Service operation.
 */
class CancelarNfseFactory extends NotaCariocaFactoryBase implements XmlFactoryInterface
{
    public function __construct(array $rps, string $env = 'dev')
    {
        parent::__construct($rps, $env);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation(): string
    {
        return 'CancelarNfse';
    }

    /**
     * {@inheritdoc}
     */
    public function formatSuccessResponse(string $responseXml): array
    {
        $resp = $this->getEncoder()->decode($responseXml, '');

        if (isset($resp['Cancelamento']) and isset($resp['Cancelamento']['Confirmacao'])) {
            return [
                'DataHoraCancelamento' => $resp['Cancelamento']['Confirmacao']['DataHoraCancelamento'],
                'Pedido' => $resp['Cancelamento']['Confirmacao']['@Id'],
            ];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaStructure(): array
    {
        return [
            'CancelarNfseEnvio' => [
                'Pedido' => [
                    'InfPedidoCancelamento' => [
                        'IdentificacaoNfse' => [
                            'Numero',
                            'Cnpj',
                            'InscricaoMunicipal',
                            'CodigoMunicipio',
                        ],
                        'CodigoCancelamento',
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvelopeXml(): string
    {
        $structure = $this->getSchemaStructure();

        $rps = [
            'CancelarNfseEnvio' => [
                '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                'Pedido' => [
                    '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                    'InfPedidoCancelamento' => [
                        'IdentificacaoNfse' => $this->rps['IdentificacaoNfse'],
                        'CodigoCancelamento' => $this->rps['CodigoCancelamento'],
                    ],
                ],
            ],
        ];

        // Validate array based on structure
        try {
            $schema = Schema::parse($structure);
            $valid = $schema->validate($rps);
        } catch (ValidationException $ex) {
            throw new \Exception(__FILE__.':'.__LINE__.' - '.$ex->getMessage());
        }

        $xml = $this->getEncoder()->encode($rps, 'xml', ['xml_root_node_name' => 'rootnode', 'remove_empty_tags' => true]);

        // clean up encode tag added by encoder
        $xml = str_replace('<?xml version="1.0"?>', '', $xml);
        $xml = str_replace('<rootnode>', '', $xml);
        $xml = str_replace('</rootnode>', '', $xml);

        // Envelope request
        $this->addEnvelope($xml);

        // header('Content-type: text/xml');
        // print_r($xml);
        // exit();

        return $xml;
    }
}
