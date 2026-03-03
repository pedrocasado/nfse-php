<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\EnderecoDTO;
use NFSePHP\DTO\TomadorDTO;
use PHPUnit\Framework\TestCase;

final class TomadorDTOTest extends TestCase
{
    public function test_construct_with_cnpj_succeeds(): void
    {
        $dto = new TomadorDTO(
            xNome: 'Empresa Teste LTDA',
            cnpj: '38744743000149',
        );

        self::assertSame('Empresa Teste LTDA', $dto->xNome);
        self::assertSame('38744743000149', $dto->cnpj);
        self::assertNull($dto->cpf);
        self::assertNull($dto->nif);
    }

    public function test_construct_with_cpf_succeeds(): void
    {
        $dto = new TomadorDTO(
            xNome: 'João Silva',
            cpf: '12345678901',
        );

        self::assertSame('12345678901', $dto->cpf);
        self::assertNull($dto->cnpj);
    }

    public function test_construct_with_nif_succeeds(): void
    {
        $dto = new TomadorDTO(
            xNome: 'Foreign Company',
            nif: 'PT123456789',
        );

        self::assertSame('PT123456789', $dto->nif);
    }

    public function test_construct_with_c_nao_nif_succeeds(): void
    {
        $dto = new TomadorDTO(
            xNome: 'Dispensado',
            cNaoNIF: '1',
        );

        self::assertSame('1', $dto->cNaoNIF);
    }

    public function test_construct_without_identifier_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ, CPF, NIF ou cNaoNIF deve ser informado');

        new TomadorDTO(
            xNome: 'Sem Identificador',
            cnpj: null,
            cpf: null,
            nif: null,
            cNaoNIF: null,
        );
    }

    public function test_construct_with_endereco(): void
    {
        $end = new EnderecoDTO(xLgr: 'Rua X', nro: '1', xBairro: 'Centro');
        $dto = new TomadorDTO(
            xNome: 'Empresa',
            cnpj: '38744743000149',
            end: $end,
            fone: '2122107277',
            email: 'contato@empresa.com',
        );

        self::assertSame($end, $dto->end);
        self::assertSame('2122107277', $dto->fone);
        self::assertSame('contato@empresa.com', $dto->email);
    }

    public function test_json_serialize_returns_all_properties(): void
    {
        $dto = new TomadorDTO(xNome: 'Teste', cnpj: '38744743000149');

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('xNome', $data);
        self::assertArrayHasKey('cnpj', $data);
        self::assertSame('Teste', $data['xNome']);
    }

    public function test_to_string_returns_json(): void
    {
        $dto = new TomadorDTO(xNome: 'Teste', cnpj: '38744743000149');

        self::assertJson((string) $dto);
    }
}
