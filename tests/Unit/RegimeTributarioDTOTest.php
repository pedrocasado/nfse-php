<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\RegimeTributarioDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class RegimeTributarioDTOTest extends TestCase
{
    public function test_construct_default_values(): void
    {
        $dto = new RegimeTributarioDTO();

        self::assertSame('1', $dto->opSimpNac);
        self::assertSame('0', $dto->regEspTrib);
        self::assertNull($dto->regApTribSN);
    }

    public function test_construct_with_explicit_values(): void
    {
        $dto = new RegimeTributarioDTO(
            opSimpNac: '3',
            regEspTrib: '5',
            regApTribSN: '2',
        );

        self::assertSame('3', $dto->opSimpNac);
        self::assertSame('5', $dto->regEspTrib);
        self::assertSame('2', $dto->regApTribSN);
    }

    public function test_json_serialize_returns_all_properties(): void
    {
        $dto = new RegimeTributarioDTO();

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('opSimpNac', $data);
        self::assertArrayHasKey('regEspTrib', $data);
        self::assertArrayHasKey('regApTribSN', $data);
    }

    public function test_to_string_returns_json(): void
    {
        $dto = new RegimeTributarioDTO();

        self::assertJson((string) $dto);
    }

    public function test_validation_fails_for_invalid_op_simp_nac(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new RegimeTributarioDTO(opSimpNac: '9', regEspTrib: '0');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }

    public function test_validation_fails_for_invalid_reg_esp_trib(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new RegimeTributarioDTO(opSimpNac: '1', regEspTrib: '99');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
