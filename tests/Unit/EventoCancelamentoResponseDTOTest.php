<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EventoCancelamentoResponseDTO;
use PHPUnit\Framework\TestCase;

final class EventoCancelamentoResponseDTOTest extends TestCase
{
    public function testFromJsonParsesSuccessResponse(): void
    {
        $json = <<<'JSON'
            {
                "tipoAmbiente": 2,
                "versaoAplicativo": "SefinNacional_1.6.0",
                "dataHoraProcessamento": "2026-02-27T19:31:59.0971392-03:00",
                "eventoXmlGZipB64": "H4sIAAAAAAAA/6tWKkktLlGyUlAqSS0u0QHQPwgEAAAA"
            }
            JSON;

        $dto = EventoCancelamentoResponseDTO::fromJson($json);

        self::assertSame(2, $dto->tipoAmbiente);
        self::assertSame('SefinNacional_1.6.0', $dto->versaoAplicativo);
        self::assertSame('2026-02-27T19:31:59.0971392-03:00', $dto->dataHoraProcessamento);
        self::assertNotNull($dto->eventoXmlGZipB64);
    }

    public function testFromJsonParsesErrorResponseWithoutEventoXml(): void
    {
        $json = <<<'JSON'
            {
                "tipoAmbiente": 2,
                "versaoAplicativo": "SefinNacional_1.6.0",
                "dataHoraProcessamento": "2026-02-27T19:31:59-03:00",
                "erro": [{"codigo": "RNG6110", "descricao": "Falha Schema Xml"}]
            }
            JSON;

        $dto = EventoCancelamentoResponseDTO::fromJson($json);

        self::assertSame(2, $dto->tipoAmbiente);
        self::assertNull($dto->eventoXmlGZipB64);
    }

    public function testFromArrayHandlesMissingFields(): void
    {
        $dto = EventoCancelamentoResponseDTO::fromArray([]);

        self::assertSame(0, $dto->tipoAmbiente);
        self::assertSame('', $dto->versaoAplicativo);
        self::assertSame('', $dto->dataHoraProcessamento);
        self::assertNull($dto->eventoXmlGZipB64);
    }

    public function testGetEventoXmlReturnsNullWhenNoBase64(): void
    {
        $dto = new EventoCancelamentoResponseDTO(
            tipoAmbiente: 2,
            versaoAplicativo: '1.0',
            dataHoraProcessamento: '2026-02-27T19:00:00-03:00',
            eventoXmlGZipB64: null,
        );

        self::assertNull($dto->getEventoXml());
    }

    public function testGetEventoXmlDecodesValidGzipBase64(): void
    {
        $xml = '<evento xmlns="http://www.sped.fazenda.gov.br/nfse"><test/></evento>';
        $gzip = gzencode($xml, 1);
        $b64 = base64_encode($gzip);

        $dto = new EventoCancelamentoResponseDTO(
            tipoAmbiente: 2,
            versaoAplicativo: '1.0',
            dataHoraProcessamento: '2026-02-27T19:00:00-03:00',
            eventoXmlGZipB64: $b64,
        );

        self::assertSame($xml, $dto->getEventoXml());
    }
}
