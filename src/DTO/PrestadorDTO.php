<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Prestador de Serviços.
 */
class PrestadorDTO implements \JsonSerializable
{
    public function __construct(
        // Regime Tributário (required)
        #[Assert\NotBlank]
        #[Assert\Valid]
        public readonly RegimeTributarioDTO $regTrib,

        // 0 - Não informado na nota de origem;
        // 1 - Dispensado do NIF;
        // 2 - Não exigência do NIF;
        public readonly ?string $cNaoNIF = null,

        // CNPJ, CPF, NIF ou cNaoNIF (choice - um dos quatro)
        public readonly ?string $cnpj = null,
        public readonly ?string $cpf = null,
        public readonly ?string $nif = null,
        public readonly ?string $caepf = null,
        public readonly ?string $im = null,
        public readonly ?string $xNome = null,
        #[Assert\Valid]
        public readonly ?EnderecoDTO $end = null,
        public readonly ?string $fone = null,
        public readonly ?string $email = null,
    ) {
        // Validar que pelo menos um identificador está presente
        if (null === $this->cnpj && null === $this->cpf && null === $this->nif && null === $this->cNaoNIF) {
            throw new \InvalidArgumentException('CNPJ, CPF, NIF ou cNaoNIF deve ser informado');
        }
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
