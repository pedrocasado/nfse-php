<?php

namespace NFSePHP\DTO;

/**
 * Result of calling the Evento (cancelamento) API.
 */
class EventoResponse
{
    public function __construct(
        public int $statusCode,
        public string $rawBody,
        public ?EventoCancelamentoResponseDTO $response = null,
    ) {
    }

    public function isHttpSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function hasParsedResponse(): bool
    {
        return null !== $this->response;
    }
}
