<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EventoConsultaItemDTO;
use PHPUnit\Framework\TestCase;

final class EventoConsultaItemDTOTest extends TestCase
{
    private const SAMPLE = [
        'chaveAcesso' => '33045572238744743000149000000000001126025596416790',
        'tipoEvento' => 101101,
        'numeroPedidoRegistroEvento' => 1,
        'dataHoraRecebimento' => '2026-02-27T19:42:04.19',
        'arquivoXml' => 'eG1sPmV4YW1wbGU8L3htbD4=',
    ];

    public function testFromArrayParsesAllFields(): void
    {
        $dto = EventoConsultaItemDTO::fromArray(self::SAMPLE);

        self::assertSame('33045572238744743000149000000000001126025596416790', $dto->chaveAcesso);
        self::assertSame(101101, $dto->tipoEvento);
        self::assertSame(1, $dto->numeroPedidoRegistroEvento);
        self::assertSame('2026-02-27T19:42:04.19', $dto->dataHoraRecebimento);
        self::assertSame('eG1sPmV4YW1wbGU8L3htbD4=', $dto->arquivoXml);
    }

    public function testFromArrayHandlesMissingFields(): void
    {
        $dto = EventoConsultaItemDTO::fromArray([]);

        self::assertSame('', $dto->chaveAcesso);
        self::assertSame(0, $dto->tipoEvento);
        self::assertSame(0, $dto->numeroPedidoRegistroEvento);
        self::assertSame('', $dto->dataHoraRecebimento);
        self::assertSame('', $dto->arquivoXml);
    }

    public function testConstructStoresValues(): void
    {
        $dto = new EventoConsultaItemDTO(
            chaveAcesso: 'chave123',
            tipoEvento: 101102,
            numeroPedidoRegistroEvento: 2,
            dataHoraRecebimento: '2026-03-01T10:00:00',
            arquivoXml: 'YmFzZTY0',
        );

        self::assertSame('chave123', $dto->chaveAcesso);
        self::assertSame(101102, $dto->tipoEvento);
        self::assertSame(2, $dto->numeroPedidoRegistroEvento);
    }
}
