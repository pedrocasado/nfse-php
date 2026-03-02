<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Informações da DPS.
 */
class InfDpsDTO implements \JsonSerializable
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['1', '2'])]
        public readonly string $tpAmb, // 1 = Produção, 2 = Homologação

        #[Assert\NotBlank]
        public readonly string $dhEmi,

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^0{0,4}\d{1,5}$/')]
        public readonly string $serie, // 1-99999, não pode ser 0

        #[Assert\NotBlank]
        public readonly string $nDPS, // Número do DPS

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\d{4}-\d{2}-\d{2}$/')]
        public readonly string $dCompet, // Data competência AAAA-MM-DD

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['1', '2', '3'])]
        public readonly string $tpEmit, // 1 = Prestador, 2 = Tomador, 3 = Intermediário

        #[Assert\NotBlank]
        public readonly string $cLocEmi, // Código IBGE município emissor

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
        public readonly ?TomadorDTO $toma = null,

        #[Assert\NotBlank]
        public readonly ?string $versao = '1.01',

        #[Assert\NotBlank]
        public readonly ?string $verAplic = '1.00',

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
