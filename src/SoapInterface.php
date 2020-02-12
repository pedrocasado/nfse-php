<?php

namespace NFSePHP;

interface SoapInterface
{
    /**
     * Send request to Web Service.
     *
     * @param XmlFactoryInterface $notaCariocaFactory
     *
     * @return void
     */
    public function send(XmlFactoryInterface $xml): string;

    /**
     * Check if response has success xml.
     *
     * @param string $responseXml
     */
    public function isSuccess(string $response): bool;

    /**
     * Get response errors.
     *
     * @param string $responseXml
     */
    public function getErrors(string $response): array;
}
