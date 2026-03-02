<?php

namespace NFSePHP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Evento de Cancelamento de NFS-e (e101101).
 *
 * This represents the data needed to build the pedido de registro
 * do evento de cancelamento according to tiposEventos_v1.01.xsd.
 */
class EventoCancelamentoDTO implements \JsonSerializable
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['1', '2'])]
        public readonly string $tpAmb, // 1 = Produção, 2 = Homologação

        #[Assert\NotBlank]
        public readonly string $dhEvento, // AAAA-MM-DDThh:mm:ssTZD (UTC)

        #[Assert\NotBlank]
        public readonly string $chNFSe, // Chave de acesso da NFS-e a cancelar

        /**
         * Código de justificativa de cancelamento (TSCodJustCanc).
         */
        #[Assert\NotBlank]
        public readonly string $cMotivo,

        /**
         * Descrição para explicitar o motivo indicado neste evento.
         */
        #[Assert\NotBlank]
        public readonly string $xMotivo,

        // Identificação do autor (um dos dois)
        public readonly ?string $cnpjAutor = null,
        public readonly ?string $cpfAutor = null,

        /**
         * Sequencial do evento para o mesmo tipo de evento.
         * Para cancelamento normalmente = 1.
         */
        #[Assert\NotBlank]
        public readonly string $nSeqEvento = '1',

        /**
         * Número do pedido do registro de evento.
         * Para cancelamento normalmente = 1.
         */
        #[Assert\NotBlank]
        public readonly string $nPedRegEvento = '1',

        /**
         * Versão do leiaute do evento (atributo @versao em <evento> e <pedRegEvento>).
         */
        #[Assert\NotBlank]
        public readonly string $versao = '1.01',

        #[Assert\NotBlank]
        public readonly string $verAplic = '1.01', // Versão do aplicativo
    ) {
        if (null === $this->cnpjAutor && null === $this->cpfAutor) {
            throw new \InvalidArgumentException('EventoCancelamentoDTO: cnpjAutor or cpfAutor must be informed');
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
