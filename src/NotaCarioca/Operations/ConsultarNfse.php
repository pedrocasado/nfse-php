<?php

namespace NFSePHP\NotaCarioca\Operations;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use NFSePHP\NotaCarioca\NotaCariocaOperationBase;

/**
 * Class to generate XML to the ConsultarNfse Web Service operation.
 */
class ConsultarNfse extends NotaCariocaOperationBase
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
     * @throws \Exception
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
            $schema->validate($rps);
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
