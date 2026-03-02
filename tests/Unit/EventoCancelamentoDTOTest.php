<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EventoCancelamentoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EventoCancelamentoDTOTest extends TestCase
{
    public function test_construct_with_cnpj_succeeds(): void
    {
        $dto = new EventoCancelamentoDTO(
            tpAmb: '2',
            dhEvento: '2026-02-27T19:00:00-03:00',
            chNFSe: '33045572210738989000199000000000001026029316934590',
            cMotivo: '2',
            xMotivo: 'Cancelamento de teste para validacao',
            cnpjAutor: '10738989000199',
        );

        self::assertSame('2', $dto->tpAmb);
        self::assertSame('10738989000199', $dto->cnpjAutor);
        self::assertNull($dto->cpfAutor);
        self::assertSame('1', $dto->nSeqEvento);
        self::assertSame('1.01', $dto->verAplic);
    }

    public function test_construct_with_cpf_succeeds(): void
    {
        $dto = new EventoCancelamentoDTO(
            tpAmb: '1',
            dhEvento: '2026-02-27T19:00:00-03:00',
            chNFSe: '33045572210738989000199000000000001026029316934590',
            cMotivo: '2',
            xMotivo: 'Motivo com mais de 15 caracteres obrigatorios',
            cpfAutor: '12345678901',
        );

        self::assertNull($dto->cnpjAutor);
        self::assertSame('12345678901', $dto->cpfAutor);
    }

    public function test_construct_without_cnpj_or_cpf_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cnpjAutor or cpfAutor must be informed');

        new EventoCancelamentoDTO(
            tpAmb: '2',
            dhEvento: '2026-02-27T19:00:00-03:00',
            chNFSe: '33045572210738989000199000000000001026029316934590',
            cMotivo: '2',
            xMotivo: 'Motivo valido com tamanho minimo',
            cnpjAutor: null,
            cpfAutor: null,
        );
    }

    public function test_json_serialize_returns_all_properties(): void
    {
        $dto = new EventoCancelamentoDTO(
            tpAmb: '2',
            dhEvento: '2026-02-27T19:00:00-03:00',
            chNFSe: '33045572210738989000199000000000001026029316934590',
            cMotivo: '2',
            xMotivo: 'Motivo de cancelamento de teste',
            cnpjAutor: '10738989000199',
        );

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('tpAmb', $data);
        self::assertArrayHasKey('chNFSe', $data);
        self::assertArrayHasKey('cMotivo', $data);
        self::assertArrayHasKey('xMotivo', $data);
        self::assertArrayHasKey('cnpjAutor', $data);
    }

    public function test_validation_fails_for_invalid_tp_amb(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new EventoCancelamentoDTO(
            tpAmb: '9',
            dhEvento: '2026-02-27T19:00:00-03:00',
            chNFSe: '33045572210738989000199000000000001026029316934590',
            cMotivo: '2',
            xMotivo: 'Motivo com tamanho minimo de 15 chars',
            cnpjAutor: '10738989000199',
        );

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
