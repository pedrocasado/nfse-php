<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Código do Serviço.
 */
class CodigoServicoDTO implements \JsonSerializable
{
    public function __construct(
        // https://www.gov.br/nfse/pt-br/mei-e-demais-empresas/codigos-de-tributacao-nacional-nbs
        #[Assert\NotBlank]
        public readonly string $cTribNac, // Código tributação nacional

        #[Assert\NotBlank]
        public readonly string $xDescServ, // Descrição do serviço

        public readonly ?string $cNBS = null, // Código NBS

        public readonly ?string $cTribMun = null, // Código tributação municipal

        public readonly ?string $cIntContrib = null, // Código internacional contribuição
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
