<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Endereço (pode ser nacional ou externo).
 */
class EnderecoDTO implements \JsonSerializable
{
    public function __construct(
        // Campos comuns (required se endereço presente)
        #[Assert\NotBlank]
        public readonly string $xLgr, // Logradouro

        #[Assert\NotBlank]
        public readonly string $nro, // Número

        #[Assert\NotBlank]
        public readonly string $xBairro, // Bairro

        public readonly ?string $xCpl = null, // Complemento

        // Endereço Nacional (opcional)
        #[Assert\Valid]
        public readonly ?EnderecoNacionalSimplificadoDTO $endNac = null,
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
