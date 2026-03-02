<?php

namespace NFSePHP\DTO;

/**
 * Parsed body of GET nfse/{chaveAcesso}/eventos/{tipoEvento}/{numSeqEvento}.
 */
final class EventosConsultaDTO
{
    /**
     * @param list<EventoConsultaItemDTO> $eventos
     */
    public function __construct(
        public readonly string $dataHoraProcessamento,
        public readonly int $tipoAmbiente,
        public readonly string $versaoAplicativo,
        /** @var list<EventoConsultaItemDTO> */
        public readonly array $eventos,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $eventos = [];
        foreach ($data['eventos'] ?? [] as $item) {
            if (\is_array($item)) {
                $eventos[] = EventoConsultaItemDTO::fromArray($item);
            }
        }

        return new self(
            dataHoraProcessamento: (string) ($data['dataHoraProcessamento'] ?? ''),
            tipoAmbiente: (int) ($data['tipoAmbiente'] ?? 0),
            versaoAplicativo: (string) ($data['versaoAplicativo'] ?? ''),
            eventos: $eventos,
        );
    }
}
