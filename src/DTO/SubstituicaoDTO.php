<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Substituição de NFS-e (TCSubstituicao from DPS_v1.01.xsd).
 * Dados da NFS-e a ser substituída.
 */
class SubstituicaoDTO implements \JsonSerializable
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^[0-9]{50}$/')]
        public readonly string $chSubstda,

        /**
         * Código de justificativa para substituição (TSCodJustSubst): 01, 02, 03, 04, 05, 99.
         */
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['01', '02', '03', '04', '05', '99'])]
        public readonly string $cMotivo,

        /**
         * Descrição do motivo (TSMotivo, 15-255 chars). Opcional para cMotivo 01-05.
         */
        #[Assert\Length(min: 15, max: 255)]
        public readonly ?string $xMotivo = null,
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
