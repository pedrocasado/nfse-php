<?php

namespace NFSePHP;

interface XmlFactoryInterface
{
    /**
     * Get the xml with envelope ready to send to the Web Service.
     *
     * @return void
     */
    public function getEnvelopeXml(): string;

    /**
     * Get schema structure of the Web Service action.
     */
    public function getSchemaStructure(): array;

    /**
     * Get the action to be used in the Web Service.
     */
    public function getAction(): string;

    /**
     * Get Web Service operation.
     */
    public function getOperation(): string;

    /**
     * Get Web Service endpoint url.
     */
    public function getEndpointUrl(): string;

    /**
     * Get environment.
     */
    public function getEnv(): string;

    /**
     * Format xml success response to array.
     */
    public function formatSuccessResponse(string $responseXml): array;
}
