<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Serviço Prestado.
 */
class ServicoDTO implements \JsonSerializable
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly LocalPrestacaoDTO $locPrest,

        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly CodigoServicoDTO $cServ,
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
