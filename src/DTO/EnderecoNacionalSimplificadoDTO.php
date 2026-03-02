<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Endereço Nacional Simplificado (usado em endereços dentro de DPS).
 */
class EnderecoNacionalSimplificadoDTO implements \JsonSerializable
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $cMun, // Código Município IBGE

        #[Assert\NotBlank]
        #[Assert\Length(exactly: 8)]
        public readonly string $CEP, // uppercase
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
