<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EnderecoNacionalSimplificadoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EnderecoNacionalSimplificadoDTOTest extends TestCase
{
    public function test_construct_stores_properties(): void
    {
        $dto = new EnderecoNacionalSimplificadoDTO(
            cMun: '3304557',
            CEP: '22451060',
        );

        self::assertSame('3304557', $dto->cMun);
        self::assertSame('22451060', $dto->CEP);
    }

    public function test_json_serialize_returns_all_properties(): void
    {
        $dto = new EnderecoNacionalSimplificadoDTO(cMun: '3304557', CEP: '22451060');

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('cMun', $data);
        self::assertArrayHasKey('CEP', $data);
        self::assertSame('3304557', $data['cMun']);
    }

    public function test_to_string_returns_json(): void
    {
        $dto = new EnderecoNacionalSimplificadoDTO(cMun: '3304557', CEP: '22451060');

        self::assertJson((string) $dto);
    }

    public function test_validation_fails_for_cep_wrong_length(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new EnderecoNacionalSimplificadoDTO(cMun: '3304557', CEP: '1234567');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
