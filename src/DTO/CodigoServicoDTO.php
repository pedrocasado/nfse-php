<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Código do Serviço.
 */
class CodigoServicoDTO implements \JsonSerializable
{
    /**
     * TCCServ XSD order: cTribNac, cTribMun, xDescServ, cNBS, cIntContrib
     * Constructor keeps xDescServ as 2nd param for backward compatibility with positional calls.
     */
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $cTribNac,

        #[Assert\NotBlank]
        public readonly string $xDescServ,

        public readonly ?string $cNBS = null,

        public readonly ?string $cTribMun = null,

        public readonly ?string $cIntContrib = null, // Código interno contribuinte
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT);
    }

    public function jsonSerialize(): array
    {
        // Return in TCCServ XSD order: cTribNac, cTribMun, xDescServ, cNBS, cIntContrib
        return [
            'cTribNac' => $this->cTribNac,
            'cTribMun' => $this->cTribMun,
            'xDescServ' => $this->xDescServ,
            'cNBS' => $this->cNBS,
            'cIntContrib' => $this->cIntContrib,
        ];
    }
}
