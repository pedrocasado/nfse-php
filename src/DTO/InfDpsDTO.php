<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Informações da DPS.
 */
class InfDpsDTO implements \JsonSerializable
{
    /**
     * Constructor. Required params follow TCInfDPS order; optional params last (PHP requirement).
     * XSD element order: tpAmb, dhEmi, verAplic, serie, nDPS, dCompet, tpEmit, cMotivoEmisTI,
     * chNFSeRej, cLocEmi, subst, prest, toma, interm, serv, valores.
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['1', '2'])]
        public readonly string $tpAmb,

        #[Assert\NotBlank]
        public readonly string $dhEmi,

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^0{0,4}\d{1,5}$/')]
        public readonly string $serie,

        #[Assert\NotBlank]
        public readonly string $nDPS,

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\d{4}-\d{2}-\d{2}$/')]
        public readonly string $dCompet,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['1', '2', '3'])]
        public readonly string $tpEmit,

        #[Assert\NotBlank]
        public readonly string $cLocEmi,

        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly PrestadorDTO $prest,

        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly ServicoDTO $serv,

        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly ValoresServicoDTO $valores,

        #[Assert\Valid]
        public readonly ?SubstituicaoDTO $subst = null,
        #[Assert\Valid]
        public readonly ?TomadorDTO $toma = null,
        #[Assert\Valid]
        public readonly ?IntermediarioDTO $interm = null,
        #[Assert\NotBlank]
        public readonly ?string $verAplic = '1.00',
        public readonly ?string $versao = '1.01',
        public readonly ?string $cMotivoEmisTI = null,
        public readonly ?string $chNFSeRej = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function toJson(int $flags = \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT): string
    {
        return json_encode($this->jsonSerialize(), $flags);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
