<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Informações da DPS.
 */
class InfDpsDTO implements \JsonSerializable
{
    public function __construct(
        // #[Assert\NotBlank]
        // public readonly string $id,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['1', '2'])]
        public readonly string $tpAmb, // 1 = Produção, 2 = Homologação

        #[Assert\NotBlank]
        public readonly string $dhEmi,

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^[1-9]\d{0,4}$/')]
        public readonly string $serie, // 1-99999, não pode ser 0

        #[Assert\NotBlank]
        public readonly string $nDPS, // Número do DPS

        #[Assert\NotBlank]
        // #[Assert\Regex(pattern: '/^\d{4}-\d{2}-\d{2}$/')]
        public readonly string $dCompet, // Data competência AAAA-MM-DD

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['1', '2', '3'])]
        public readonly string $tpEmit, // 1 = Prestador, 2 = Tomador, 3 = Intermediário

        #[Assert\NotBlank]
        public readonly string $cLocEmi, // Código IBGE município emissor

        // Prestador (required)
        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly PrestadorDTO $prest,

        // Serviço (required)
        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly ServicoDTO $serv,

        // Valores (required)
        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly ValoresServicoDTO $valores,

        // Tomador (optional)
        #[Assert\Valid]
        public readonly ?TomadorDTO $toma = null,

        // Intermediário (optional)
        // public readonly ?IntermediarioDTO $interm = null,

        #[Assert\NotBlank]
        public readonly ?string $versao = '1.01',

        #[Assert\NotBlank]
        public readonly ?string $verAplic = '1.00',

        // Opcionais
        public readonly ?string $cMotivoEmisTI = null,
        public readonly ?string $chNFSeRej = null,
        public readonly ?SubstituicaoDTO $subst = null,
        public readonly ?IbsCbsInfoDTO $ibscbs = null,
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
