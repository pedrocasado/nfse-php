<?php

namespace NFSePHP\NotaCarioca;

use NFSePHP\XmlFactoryInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

abstract class NotaCariocaFactoryBase implements XmlFactoryInterface
{
    const BASE_ACTION_URL = 'http://notacarioca.rio.gov.br/';

    /**
     * @var array
     */
    protected $rps;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var XmlEncoder
     */
    protected $encoder;

    public function __construct(array $rps, string $env = 'dev')
    {
        $this->rps = $rps;
        $this->env = $env;
        $this->encoder = new XmlEncoder();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnv(): string
    {
        return $this->env;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpointUrl(string $env = 'dev'): string
    {
        $hml = 'prod' != $env ? 'homologacao.' : '';

        return 'https://'.$hml.'notacarioca.rio.gov.br/WSNacional/nfse.asmx';
    }

    /**
     * {@inheritdoc}
     */
    public function getAction(): string
    {
        return self::BASE_ACTION_URL.$this->getOperation();
    }

    /**
     * Get XML encoder.
     *
     * @return XmlEncoder
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * Add SOAP envelope to XML.
     *
     * @return string
     */
    public function addEnvelope(string &$content)
    {
        $actionRequest = $this->getOperation().'Request';

        $env = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <'.$actionRequest.' xmlns="http://notacarioca.rio.gov.br/">
                    <inputXML>
                    <![CDATA[
                        PLACEHOLDER
                    ]]>
                    </inputXML>
                </'.$actionRequest.'>
            </soap:Body>
        </soap:Envelope>';

        $content = str_replace('PLACEHOLDER', $content, $env);
    }
}
