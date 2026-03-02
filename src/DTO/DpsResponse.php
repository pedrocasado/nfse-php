<?php

namespace NFSePHP\DTO;

/**
 * Result of sending DPS to Sefin Nacional (HTTP status + parsed body when JSON).
 */
class DpsResponse
{
    public function __construct(
        public int $statusCode,
        public string $rawBody,
        public ?SefinNacionalResponse $response = null,
    ) {
    }

    public function isHttpSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Response body was valid JSON and could be parsed as SefinNacionalResponse.
     */
    public function hasParsedResponse(): bool
    {
        return null !== $this->response;
    }
}
