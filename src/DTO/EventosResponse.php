<?php

namespace NFSePHP\DTO;

/**
 * Result of GET nfse/{chaveAcesso}/eventos/{tipoEvento}/{numSeqEvento}.
 */
class EventosResponse
{
    public function __construct(
        public int $statusCode,
        public string $rawBody,
        public ?EventosConsultaDTO $response = null,
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
