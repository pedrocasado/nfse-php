<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EnderecoDTO;
use NFSePHP\DTO\PrestadorDTO;
use NFSePHP\DTO\RegimeTributarioDTO;
use PHPUnit\Framework\TestCase;

final class PrestadorDTOTest extends TestCase
{
    public function testConstructWithCnpjSucceeds(): void
    {
        $regTrib = new RegimeTributarioDTO();
        $dto = new PrestadorDTO(
            regTrib: $regTrib,
            cnpj: '38744743000149',
        );

        self::assertSame($regTrib, $dto->regTrib);
        self::assertSame('38744743000149', $dto->cnpj);
        self::assertNull($dto->cpf);
    }

    public function testConstructWithCpfSucceeds(): void
    {
        $regTrib = new RegimeTributarioDTO();
        $dto = new PrestadorDTO(
            regTrib: $regTrib,
            cpf: '12345678901',
        );

        self::assertSame('12345678901', $dto->cpf);
    }

    public function testConstructWithNifSucceeds(): void
    {
        $regTrib = new RegimeTributarioDTO();
        $dto = new PrestadorDTO(
            regTrib: $regTrib,
            nif: 'PT123456789',
        );

        self::assertSame('PT123456789', $dto->nif);
    }

    public function testConstructWithCNaoNifSucceeds(): void
    {
        $regTrib = new RegimeTributarioDTO();
        $dto = new PrestadorDTO(
            regTrib: $regTrib,
            cNaoNIF: '1',
        );

        self::assertSame('1', $dto->cNaoNIF);
    }

    public function testConstructWithoutIdentifierThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ, CPF, NIF ou cNaoNIF deve ser informado');

        new PrestadorDTO(
            regTrib: new RegimeTributarioDTO(),
            cnpj: null,
            cpf: null,
            nif: null,
            cNaoNIF: null,
        );
    }

    public function testConstructWithOptionalFields(): void
    {
        $regTrib = new RegimeTributarioDTO();
        $end = new EnderecoDTO(xLgr: 'Rua X', nro: '1', xBairro: 'Centro');
        $dto = new PrestadorDTO(
            regTrib: $regTrib,
            cnpj: '38744743000149',
            xNome: 'Empresa LTDA',
            end: $end,
            fone: '2122107277',
            email: 'contato@empresa.com',
        );

        self::assertSame('Empresa LTDA', $dto->xNome);
        self::assertSame($end, $dto->end);
        self::assertSame('2122107277', $dto->fone);
        self::assertSame('contato@empresa.com', $dto->email);
    }

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $dto = new PrestadorDTO(
            regTrib: new RegimeTributarioDTO(),
            cnpj: '38744743000149',
        );

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('regTrib', $data);
        self::assertArrayHasKey('cnpj', $data);
    }

    public function testToStringReturnsJson(): void
    {
        $dto = new PrestadorDTO(
            regTrib: new RegimeTributarioDTO(),
            cnpj: '38744743000149',
        );

        self::assertJson((string) $dto);
    }
}
