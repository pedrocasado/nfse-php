<?php

namespace NFSePHP\DTO;

/**
 * Single event item in GET eventos response (eventos[]).
 */
final class EventoConsultaItemDTO
{
    public function __construct(
        public readonly string $chaveAcesso,
        public readonly int $tipoEvento,
        public readonly int $numeroPedidoRegistroEvento,
        public readonly string $dataHoraRecebimento,
        /** Base64-encoded (optionally gzipped) event XML */
        public readonly string $arquivoXml,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            chaveAcesso: (string) ($data['chaveAcesso'] ?? ''),
            tipoEvento: (int) ($data['tipoEvento'] ?? 0),
            numeroPedidoRegistroEvento: (int) ($data['numeroPedidoRegistroEvento'] ?? 0),
            dataHoraRecebimento: (string) ($data['dataHoraRecebimento'] ?? ''),
            arquivoXml: (string) ($data['arquivoXml'] ?? ''),
        );
    }
}
