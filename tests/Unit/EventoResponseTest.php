<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EventoCancelamentoResponseDTO;
use NFSePHP\DTO\EventoResponse;
use PHPUnit\Framework\TestCase;

final class EventoResponseTest extends TestCase
{
    public function testIsHttpSuccessTrueFor2xx(): void
    {
        $response = new EventoResponse(200, '{}', null);
        self::assertTrue($response->isHttpSuccess());

        $response = new EventoResponse(201, '{}', null);
        self::assertTrue($response->isHttpSuccess());
    }

    public function testIsHttpSuccessFalseFor4xxAnd5xx(): void
    {
        self::assertFalse((new EventoResponse(400, '{}', null))->isHttpSuccess());
        self::assertFalse((new EventoResponse(500, '{}', null))->isHttpSuccess());
    }

    public function testHasParsedResponseTrueWhenDtoPresent(): void
    {
        $dto = new EventoCancelamentoResponseDTO(2, '1.0', '2026-02-27T19:00:00-03:00', null);
        $response = new EventoResponse(200, '{}', $dto);
        self::assertTrue($response->hasParsedResponse());
    }

    public function testHasParsedResponseFalseWhenBodyNotJson(): void
    {
        $response = new EventoResponse(200, '<html>error</html>', null);
        self::assertFalse($response->hasParsedResponse());
    }
}
