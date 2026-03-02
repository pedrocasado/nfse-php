<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\DpsResponse;
use NFSePHP\DTO\SefinNacionalResponse;
use PHPUnit\Framework\TestCase;

final class DpsResponseTest extends TestCase
{
    public function test_is_http_success_true_for_2xx(): void
    {
        self::assertTrue((new DpsResponse(200, '{}', null))->isHttpSuccess());
        self::assertTrue((new DpsResponse(201, '{}', null))->isHttpSuccess());
    }

    public function test_is_http_success_false_for_4xx_and_5xx(): void
    {
        self::assertFalse((new DpsResponse(400, '{}', null))->isHttpSuccess());
        self::assertFalse((new DpsResponse(500, '{}', null))->isHttpSuccess());
    }

    public function test_has_parsed_response_true_when_sefin_dto_present(): void
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

    public function test_has_parsed_response_false_when_body_not_json(): void
    {
        $response = new DpsResponse(200, '<html>error</html>', null);

        self::assertFalse($response->hasParsedResponse());
        self::assertNull($response->response);
    }
}
