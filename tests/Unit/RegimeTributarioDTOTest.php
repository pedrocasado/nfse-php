<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\RegimeTributarioDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class RegimeTributarioDTOTest extends TestCase
{
    public function testConstructDefaultValues(): void
    {
        $dto = new RegimeTributarioDTO();

        self::assertSame('1', $dto->opSimpNac);
        self::assertSame('0', $dto->regEspTrib);
        self::assertNull($dto->regApTribSN);
    }

    public function testConstructWithExplicitValues(): void
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

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $dto = new RegimeTributarioDTO();

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('opSimpNac', $data);
        self::assertArrayHasKey('regEspTrib', $data);
        self::assertArrayHasKey('regApTribSN', $data);
    }

    public function testToStringReturnsJson(): void
    {
        $dto = new RegimeTributarioDTO();

        self::assertJson((string) $dto);
    }

    public function testValidationFailsForInvalidOpSimpNac(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new RegimeTributarioDTO(opSimpNac: '9', regEspTrib: '0');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForInvalidRegEspTrib(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new RegimeTributarioDTO(opSimpNac: '1', regEspTrib: '99');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
