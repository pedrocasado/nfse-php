<?php

namespace NFSePHP\DTO;

/**
 * Single error item from Sefin Nacional API response.
 */
class DpsResponseError
{
    public function __construct(
        public string $codigo,
        public string $descricao,
    ) {
    }

    public function __toString(): string
    {
        return $this->codigo.' - '.$this->descricao;
    }
}
