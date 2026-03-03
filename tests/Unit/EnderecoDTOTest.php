<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EnderecoDTO;
use NFSePHP\DTO\EnderecoNacionalSimplificadoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EnderecoDTOTest extends TestCase
{
    public function testConstructStoresAllProperties(): void
    {
        $dto = new EnderecoDTO(
            xLgr: 'Rua das Flores',
            nro: '123',
            xBairro: 'Centro',
        );

        self::assertSame('Rua das Flores', $dto->xLgr);
        self::assertSame('123', $dto->nro);
        self::assertSame('Centro', $dto->xBairro);
        self::assertNull($dto->xCpl);
        self::assertNull($dto->endNac);
    }

    public function testConstructWithOptionalFields(): void
    {
        $endNac = new EnderecoNacionalSimplificadoDTO(cMun: '3304557', CEP: '22451060');
        $dto = new EnderecoDTO(
            xLgr: 'Av. Principal',
            nro: '456',
            xBairro: 'Jardins',
            xCpl: 'Sala 101',
            endNac: $endNac,
        );

        self::assertSame('Sala 101', $dto->xCpl);
        self::assertSame($endNac, $dto->endNac);
    }

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $dto = new EnderecoDTO(
            xLgr: 'Rua Teste',
            nro: '1',
            xBairro: 'Bairro',
        );

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('xLgr', $data);
        self::assertArrayHasKey('nro', $data);
        self::assertArrayHasKey('xBairro', $data);
        self::assertArrayHasKey('xCpl', $data);
        self::assertArrayHasKey('endNac', $data);
        self::assertSame('Rua Teste', $data['xLgr']);
    }

    public function testToStringReturnsJson(): void
    {
        $dto = new EnderecoDTO(
            xLgr: 'Rua Teste',
            nro: '1',
            xBairro: 'Bairro',
        );

        $str = (string) $dto;

        self::assertJson($str);
        $decoded = json_decode($str, true);
        self::assertSame('Rua Teste', $decoded['xLgr']);
    }

    public function testValidationFailsForEmptyXLgr(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new EnderecoDTO(
            xLgr: '',
            nro: '123',
            xBairro: 'Centro',
        );

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
