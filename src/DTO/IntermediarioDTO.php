<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Intermediário de Serviços (TCInfoPessoa from tiposComplexos_v1.01.xsd).
 */
class IntermediarioDTO implements \JsonSerializable
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $xNome,

        public readonly ?string $cNaoNIF = null,
        public readonly ?string $cnpj = null,
        public readonly ?string $cpf = null,
        public readonly ?string $nif = null,
        public readonly ?string $caepf = null,
        public readonly ?string $im = null,

        #[Assert\Valid]
        public readonly ?EnderecoDTO $end = null,
        public readonly ?string $fone = null,
        public readonly ?string $email = null,
    ) {
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
