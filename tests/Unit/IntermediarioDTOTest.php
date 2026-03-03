<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EnderecoDTO;
use NFSePHP\DTO\IntermediarioDTO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for IntermediarioDTO (TCInfoPessoa from tiposComplexos_v1.01.xsd).
 * Informações do Intermediário de Serviços (elemento interm na DPS).
 */
final class IntermediarioDTOTest extends TestCase
{
    public function testConstructWithCnpjSucceeds(): void
    {
        $dto = new IntermediarioDTO(
            xNome: 'Intermediária Serviços LTDA',
            cnpj: '10738989000199',
        );

        self::assertSame('Intermediária Serviços LTDA', $dto->xNome);
        self::assertSame('10738989000199', $dto->cnpj);
        self::assertNull($dto->cpf);
        self::assertNull($dto->nif);
    }

    public function testConstructWithCpfSucceeds(): void
    {
        $dto = new IntermediarioDTO(
            xNome: 'João Intermediário',
            cpf: '12345678901',
        );

        self::assertSame('12345678901', $dto->cpf);
        self::assertNull($dto->cnpj);
    }

    public function testConstructWithNifSucceeds(): void
    {
        $dto = new IntermediarioDTO(
            xNome: 'Foreign Intermediary',
            nif: 'PT123456789',
        );

        self::assertSame('PT123456789', $dto->nif);
    }

    public function testConstructWithCNaoNifSucceeds(): void
    {
        $dto = new IntermediarioDTO(
            xNome: 'Dispensado NIF',
            cNaoNIF: '1',
        );

        self::assertSame('1', $dto->cNaoNIF);
    }

    public function testConstructWithoutIdentifierThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ, CPF, NIF ou cNaoNIF deve ser informado');

        new IntermediarioDTO(
            xNome: 'Sem Identificador',
            cnpj: null,
            cpf: null,
            nif: null,
            cNaoNIF: null,
        );
    }

    public function testConstructWithEnderecoAndContact(): void
    {
        $end = new EnderecoDTO(xLgr: 'Av. Principal', nro: '100', xBairro: 'Centro');
        $dto = new IntermediarioDTO(
            xNome: 'Intermediária',
            cnpj: '10738989000199',
            end: $end,
            fone: '2122107277',
            email: 'interm@empresa.com',
        );

        self::assertSame($end, $dto->end);
        self::assertSame('2122107277', $dto->fone);
        self::assertSame('interm@empresa.com', $dto->email);
    }

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $dto = new IntermediarioDTO(xNome: 'Teste', cnpj: '10738989000199');

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('xNome', $data);
        self::assertArrayHasKey('cnpj', $data);
    }

    public function testToStringReturnsJson(): void
    {
        $dto = new IntermediarioDTO(xNome: 'Teste', cnpj: '10738989000199');

        self::assertJson((string) $dto);
    }
}
