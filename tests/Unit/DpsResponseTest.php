<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\DpsResponse;
use NFSePHP\DTO\SefinNacionalResponse;
use PHPUnit\Framework\TestCase;

final class DpsResponseTest extends TestCase
{
    public function testIsHttpSuccessTrueFor2xx(): void
    {
        self::assertTrue((new DpsResponse(200, '{}', null))->isHttpSuccess());
        self::assertTrue((new DpsResponse(201, '{}', null))->isHttpSuccess());
    }

    public function testIsHttpSuccessFalseFor4xxAnd5xx(): void
    {
        self::assertFalse((new DpsResponse(400, '{}', null))->isHttpSuccess());
        self::assertFalse((new DpsResponse(500, '{}', null))->isHttpSuccess());
    }

    public function testHasParsedResponseTrueWhenSefinDtoPresent(): void
    {
        $sefin = SefinNacionalResponse::fromArray([
            'tipoAmbiente' => 1,
            'versaoAplicativo' => '1.0',
            'dataHoraProcessamento' => '2026-02-27T19:00:00-03:00',
            'nfseXmlGZipB64' => 'H4sIAAAAAAAA/6tWKkktLlGyUlAqSS0u0QHQPwgEAAAA',
        ]);
        $response = new DpsResponse(200, '{}', $sefin);

        self::assertTrue($response->hasParsedResponse());
        self::assertSame($sefin, $response->response);
    }

    public function testHasParsedResponseFalseWhenBodyNotJson(): void
    {
        $response = new DpsResponse(200, '<html>error</html>', null);

        self::assertFalse($response->hasParsedResponse());
        self::assertNull($response->response);
    }
}
