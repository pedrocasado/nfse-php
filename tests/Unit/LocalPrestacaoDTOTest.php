<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\LocalPrestacaoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class LocalPrestacaoDTOTest extends TestCase
{
    public function test_construct_stores_properties(): void
    {
        $dto = new LocalPrestacaoDTO(
            cLocPrestacao: '3304557',
            cPaisPrestacao: '105',
        );

        self::assertSame('3304557', $dto->cLocPrestacao);
        self::assertSame('105', $dto->cPaisPrestacao);
    }

    public function test_json_serialize_returns_all_properties(): void
    {
        $dto = new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105');

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('cLocPrestacao', $data);
        self::assertArrayHasKey('cPaisPrestacao', $data);
        self::assertSame('3304557', $data['cLocPrestacao']);
    }

    public function test_to_string_returns_json(): void
    {
        $dto = new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105');

        self::assertJson((string) $dto);
    }

    public function test_validation_fails_for_blank_c_loc_prestacao(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new LocalPrestacaoDTO(cLocPrestacao: '', cPaisPrestacao: '105');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
