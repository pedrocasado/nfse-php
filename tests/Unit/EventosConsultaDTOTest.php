<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EventoConsultaItemDTO;
use NFSePHP\DTO\EventosConsultaDTO;
use PHPUnit\Framework\TestCase;

final class EventosConsultaDTOTest extends TestCase
{
    private const SAMPLE = [
        'dataHoraProcessamento' => '2026-03-02T09:55:36.0890461-03:00',
        'tipoAmbiente' => 2,
        'versaoAplicativo' => 'SefinNacional_1.6.0',
        'eventos' => [
            [
                'chaveAcesso' => '33045572238744743000149000000000001126025596416790',
                'tipoEvento' => 101101,
                'numeroPedidoRegistroEvento' => 1,
                'dataHoraRecebimento' => '2026-02-27T19:42:04.19',
                'arquivoXml' => 'eG1sPmV4YW1wbGU8L3htbD4=',
            ],
        ],
    ];

    public function test_from_array_parses_root_and_eventos(): void
    {
        $dto = EventosConsultaDTO::fromArray(self::SAMPLE);

        self::assertSame('2026-03-02T09:55:36.0890461-03:00', $dto->dataHoraProcessamento);
        self::assertSame(2, $dto->tipoAmbiente);
        self::assertSame('SefinNacional_1.6.0', $dto->versaoAplicativo);
        self::assertCount(1, $dto->eventos);
    }

    public function test_evento_item_parses_all_fields(): void
    {
        $dto = EventosConsultaDTO::fromArray(self::SAMPLE);
        $item = $dto->eventos[0];

        self::assertInstanceOf(EventoConsultaItemDTO::class, $item);
        self::assertSame('33045572238744743000149000000000001126025596416790', $item->chaveAcesso);
        self::assertSame(101101, $item->tipoEvento);
        self::assertSame(1, $item->numeroPedidoRegistroEvento);
        self::assertSame('2026-02-27T19:42:04.19', $item->dataHoraRecebimento);
        self::assertSame('eG1sPmV4YW1wbGU8L3htbD4=', $item->arquivoXml);
    }

    public function test_from_array_empty_eventos(): void
    {
        $dto = EventosConsultaDTO::fromArray([
            'dataHoraProcessamento' => '2026-03-02T10:00:00-03:00',
            'tipoAmbiente' => 1,
            'versaoAplicativo' => '1.0',
            'eventos' => [],
        ]);

        self::assertSame([], $dto->eventos);
    }
}
