<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\DpsResponseError;
use NFSePHP\DTO\SefinNacionalResponse;
use PHPUnit\Framework\TestCase;

final class SefinNacionalResponseTest extends TestCase
{
    public function test_from_json_parses_success_response(): void
    {
        $json = <<<'JSON'
        {
            "tipoAmbiente": 1,
            "versaoAplicativo": "SefinNacional_1.6.0",
            "dataHoraProcessamento": "2026-02-27T15:05:21.4015763-03:00",
            "idDps": "550e8400-e29b-41d4-a716-446655440000",
            "chaveAcesso": "33045572238744743000149000000000001026029316934590",
            "nfseXmlGZipB64": "H4sIAAAAAAAA/6tWKkktLlGyUlAqSS0u0QHQPwgEAAAA",
            "alertas": []
        }
        JSON;

        $dto = SefinNacionalResponse::fromJson($json);

        self::assertSame(1, $dto->tipoAmbiente);
        self::assertSame('SefinNacional_1.6.0', $dto->versaoAplicativo);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $dto->idDps);
        self::assertSame('33045572238744743000149000000000001026029316934590', $dto->chaveAcesso);
        self::assertNotNull($dto->nfseXmlGZipB64);
        self::assertSame([], $dto->alertas);
        self::assertTrue($dto->isSuccess());
        self::assertFalse($dto->isError());
        self::assertSame([], $dto->getErros());
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $dto->getNfseId());
    }

    public function test_from_json_parses_error_response(): void
    {
        $json = <<<'JSON'
        {
            "tipoAmbiente": 2,
            "versaoAplicativo": "SefinNacional_1.6.0",
            "dataHoraProcessamento": "2026-02-27T19:31:59.0971392-03:00",
            "erros": [
                {
                    "Codigo": "RNG6110",
                    "Descricao": "Falha Schema Xml",
                    "Complemento": "The element 'infEvento' has invalid child element 'nDFe'."
                }
            ]
        }
        JSON;

        $dto = SefinNacionalResponse::fromJson($json);

        self::assertSame(2, $dto->tipoAmbiente);
        self::assertNull($dto->nfseXmlGZipB64);
        self::assertFalse($dto->isSuccess());
        self::assertTrue($dto->isError());

        $erros = $dto->getErros();
        self::assertCount(1, $erros);
        self::assertInstanceOf(DpsResponseError::class, $erros[0]);
        self::assertSame('RNG6110', $erros[0]->codigo);
        self::assertSame('Falha Schema Xml', $erros[0]->descricao);
    }

    public function test_from_array_parses_multiple_erros(): void
    {
        $data = [
            'tipoAmbiente' => 2,
            'versaoAplicativo' => '1.0',
            'dataHoraProcessamento' => '2026-02-27T19:00:00-03:00',
            'erros' => [
                ['Codigo' => 'ERR001', 'Descricao' => 'First error'],
                ['Codigo' => 'ERR002', 'Descricao' => 'Second error'],
            ],
        ];

        $dto = SefinNacionalResponse::fromArray($data);

        self::assertCount(2, $dto->getErros());
        self::assertSame('ERR001', $dto->getErros()[0]->codigo);
        self::assertSame('ERR002', $dto->getErros()[1]->codigo);
    }

    public function test_from_array_handles_missing_fields(): void
    {
        $dto = SefinNacionalResponse::fromArray([]);

        self::assertSame(0, $dto->tipoAmbiente);
        self::assertSame('', $dto->versaoAplicativo);
        self::assertSame('', $dto->dataHoraProcessamento);
        self::assertNull($dto->idDps);
        self::assertNull($dto->chaveAcesso);
        self::assertNull($dto->nfseXmlGZipB64);
        self::assertSame([], $dto->getErros());
        self::assertFalse($dto->isSuccess());
    }

    public function test_is_success_false_when_nfse_xml_empty(): void
    {
        $dto = new SefinNacionalResponse(
            tipoAmbiente: 1,
            versaoAplicativo: '1.0',
            dataHoraProcessamento: '2026-02-27T19:00:00-03:00',
            nfseXmlGZipB64: null,
        );

        self::assertFalse($dto->isSuccess());
        self::assertTrue($dto->isError());
    }

    public function test_is_success_false_when_erros_not_empty(): void
    {
        $dto = new SefinNacionalResponse(
            tipoAmbiente: 1,
            versaoAplicativo: '1.0',
            dataHoraProcessamento: '2026-02-27T19:00:00-03:00',
            nfseXmlGZipB64: 'H4sIAAAAAAAA/6tWKkktLlGyUlAqSS0u0QHQPwgEAAAA',
            erros: [new DpsResponseError('RNG6110', 'Falha Schema Xml')],
        );

        self::assertFalse($dto->isSuccess());
        self::assertTrue($dto->isError());
    }

    public function test_get_nfse_xml_returns_null_when_no_base64(): void
    {
        $dto = new SefinNacionalResponse(
            tipoAmbiente: 1,
            versaoAplicativo: '1.0',
            dataHoraProcessamento: '2026-02-27T19:00:00-03:00',
            nfseXmlGZipB64: null,
        );

        self::assertNull($dto->getNfseXml());
    }

    public function test_get_nfse_xml_decodes_valid_gzip_base64(): void
    {
        $xml = '<NFSe xmlns="http://www.sped.fazenda.gov.br/nfse"><infNFSe/></NFSe>';
        $gzip = gzencode($xml, 1);
        $b64 = base64_encode($gzip);

        $dto = new SefinNacionalResponse(
            tipoAmbiente: 1,
            versaoAplicativo: '1.0',
            dataHoraProcessamento: '2026-02-27T19:00:00-03:00',
            nfseXmlGZipB64: $b64,
        );

        self::assertSame($xml, $dto->getNfseXml());
    }

    public function test_from_json_parses_error_with_erro_singular_and_lowercase_keys(): void
    {
        $json = <<<'JSON'
        {
            "tipoAmbiente": 2,
            "versaoAplicativo": "SefinNacional_1.6.0",
            "dataHoraProcessamento": "2026-02-27T19:31:59.0971392-03:00",
            "erro": [
                {
                    "codigo": "RNG6110",
                    "descricao": "Falha Schema Xml",
                    "complemento": "The 'Id' attribute is invalid."
                }
            ]
        }
        JSON;

        $dto = SefinNacionalResponse::fromJson($json);

        self::assertFalse($dto->isSuccess());
        self::assertCount(1, $dto->getErros());
        self::assertSame('RNG6110', $dto->getErros()[0]->codigo);
        self::assertSame('Falha Schema Xml', $dto->getErros()[0]->descricao);
    }

    public function test_from_array_erro_item_missing_codigo_descricao(): void
    {
        $data = [
            'tipoAmbiente' => 2,
            'versaoAplicativo' => '1.0',
            'dataHoraProcessamento' => '2026-02-27T19:00:00-03:00',
            'erros' => [
                [],
                ['Codigo' => 'X', 'Descricao' => 'Y'],
            ],
        ];

        $dto = SefinNacionalResponse::fromArray($data);

        self::assertCount(2, $dto->getErros());
        self::assertSame('', $dto->getErros()[0]->codigo);
        self::assertSame('', $dto->getErros()[0]->descricao);
        self::assertSame('X', $dto->getErros()[1]->codigo);
        self::assertSame('Y', $dto->getErros()[1]->descricao);
    }
}
