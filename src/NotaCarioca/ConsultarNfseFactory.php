<?php

namespace NFSePHP\NotaCarioca;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use NFSePHP\XmlFactoryInterface;

/**
 * Class to generate XML to the ConsultarNfse Web Service operation.
 */
class ConsultarNfseFactory extends NotaCariocaFactoryBase implements XmlFactoryInterface
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
        return 'ConsultarNfse';
    }

    /**
     * {@inheritdoc}
     */
    public function formatSuccessResponse(string $responseXml): array
    {
        $resultArr = $this->getEncoder()->decode($responseXml, '');

        $responseArr = [];
        if (isset($resultArr['ListaNfse']) and isset($resultArr['ListaNfse']['CompNfse'])) {
            foreach ($resultArr['ListaNfse']['CompNfse'] as $nfse) {
                $responseArr[] = $nfse['Nfse']['InfNfse'];
            }
        }

        return $responseArr;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaStructure(): array
    {
        return [
            'ConsultarNfseEnvio' => [
                'Prestador' => ['Cnpj', 'InscricaoMunicipal'],
                'PeriodoEmissao' => ['DataInicial', 'DataFinal'],
                'Tomador' => [
                    'CpfCnpj' => [
                        'Cpf?',
                        'Cnpj?',
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
            'ConsultarNfseEnvio' => [
                '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                'Prestador' => $this->rps['Prestador'],
                'PeriodoEmissao' => $this->rps['PeriodoEmissao'],
                'Tomador' => $this->rps['Tomador'],
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
