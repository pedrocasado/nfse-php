<?php

namespace NFSePHP\NotaCarioca;

use NFSePHP\SoapInterface;
use NFSePHP\XmlInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class SoapHandler implements SoapInterface
{
    /**
     * @var XmlEncoder
     */
    protected $xmlEncoder;

    /**
     * @var string
     */
    protected $certPath;

    /**
     * @var string
     */
    protected $certPass;

    /**
     * The parameters supported are:.
     *
     * - cert_path: Certificate path (.pfx)
     * - cert_pass: (optional) Certificate password
     */
    public function __construct(array $params)
    {
        if (!isset($params['cert_path'])) {
            throw new RuntimeException('cert_path missing.');
        }

        $this->certPath = $params['cert_path'];
        $this->certPass = isset($params['cert_pass']) ? $params['cert_pass'] : null;

        $this->xmlEncoder = new XmlEncoder();
    }

    /**
     * {@inheritdoc}
     */
    public function send(XmlInterface $notaCariocaFactory): string
    {
        $operation = $notaCariocaFactory->getOperation();
        $url = $notaCariocaFactory->getEndpointUrl($notaCariocaFactory->getEnv());
        $action = $notaCariocaFactory->getAction();

        $xml = $notaCariocaFactory->getEnvelopeXml();
        $msgSize = strlen($xml);
        $headers = ['Content-Type: text/xml;charset=UTF-8', "SOAPAction: \"$action\"", "Content-length: $msgSize"];

        // Setup Curl
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 120 + 20);
        curl_setopt($oCurl, CURLOPT_HEADER, 1);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);

        $data = file_get_contents($this->certPath);
        $certPassword = $this->certPass;

        openssl_pkcs12_read($data, $certs, $certPassword);
        $pkey = $certs['pkey'];

        // Encrypt .pem file with a temporary password
        // Even if somebody finds the file it will have a random password
        // generated every run
        // $encryptPassword = uniqid();
        // openssl_pkey_export($certs['pkey'], $pkey, $encryptPassword);

        $pemPath = sys_get_temp_dir().'/'.uniqid().'.pem';
        file_put_contents($pemPath, $certs['cert'].$pkey);

        curl_setopt($oCurl, CURLOPT_SSLVERSION, 0);
        curl_setopt($oCurl, CURLOPT_SSLCERT, $pemPath);
        // curl_setopt($oCurl, CURLOPT_SSLKEY, sys_get_temp_dir() . '/file.key'); // Not necessary because both CRT and KEY are on the same file

        // Use if encrypt needed
        // curl_setopt($oCurl, CURLOPT_KEYPASSWD, $encryptPassword);

        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($oCurl);
        $soapErr = curl_error($oCurl);

        $headSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
        curl_close($oCurl);

        // Remove .pem temp file
        unlink($pemPath);

        $responseHead = trim(substr($response, 0, $headSize));
        $responseBody = trim(substr($response, $headSize));

        if ('' != $soapErr) {
            throw new \Exception($soapErr." [$url]");
        }

        if (200 != $httpCode) {
            throw new \Exception("HTTP error code: [$httpCode] - [$url] - ".$responseBody);
        }

        // header('Content-type: text/xml');
        // echo $responseBody;
        // exit();

        return $this->extractContentFromResponse($responseBody);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccess(string $responseXml): bool
    {
        $resultArr = $this->xmlEncoder->decode($responseXml, '');

        return !isset($resultArr['ListaMensagemRetorno']) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(string $responseXml): array
    {
        $resultArr = $this->xmlEncoder->decode($responseXml, '');

        if (isset($resultArr['ListaMensagemRetorno'])) {
            if (isset($resultArr['ListaMensagemRetorno']['MensagemRetorno']['Codigo'])) {
                $errors[] = $resultArr['ListaMensagemRetorno']['MensagemRetorno']['Codigo'].' - '.$resultArr['ListaMensagemRetorno']['MensagemRetorno']['Mensagem'];
            } else {
                foreach ($resultArr['ListaMensagemRetorno']['MensagemRetorno'] as $msgRetorno) {
                    $errors[] = $msgRetorno['Codigo'].' - '.$msgRetorno['Mensagem'];
                }
            }

            return $errors;
        }

        return [];
    }

    /**
     * Extract xml response from CDATA outputXML tag.
     *
     * @param string $response Return from webservice
     *
     * @return string XML extracted from response
     */
    protected function extractContentFromResponse(string $response): string
    {
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadXML($response);

        if (!empty($dom->getElementsByTagName('outputXML')->item(0))) {
            $node = $dom->getElementsByTagName('outputXML')->item(0);

            return $node->textContent;
        }

        return $response;
    }
}
