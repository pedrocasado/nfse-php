<?php

namespace NFSePHP\NotaCarioca;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use NFSePHP\XmlFactoryInterface;

/**
 * Class to generate XML to the ConsultarNfsePorRps Web Service operation.
 */
class ConsultarNfsePorRpsFactory extends NotaCariocaFactoryBase implements XmlFactoryInterface
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
        return 'ConsultarNfsePorRps';
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
            'ConsultarNfseRpsEnvio' => [
                'IdentificacaoRps' => ['Numero', 'Serie', 'Tipo'],
                'Prestador' => ['Cnpj', 'InscricaoMunicipal'],
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
            'ConsultarNfseRpsEnvio' => [
                '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                'IdentificacaoRps' => $this->rps['IdentificacaoRps'],
                'Prestador' => $this->rps['Prestador'],
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
