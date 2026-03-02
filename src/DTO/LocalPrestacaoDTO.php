<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Local de Prestação.
 */
class LocalPrestacaoDTO implements \JsonSerializable
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $cLocPrestacao, // Código IBGE local prestação

        #[Assert\NotBlank]
        public readonly string $cPaisPrestacao, // Código País (105 = Brasil)
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
