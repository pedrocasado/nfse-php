<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\CodigoServicoDTO;
use NFSePHP\DTO\LocalPrestacaoDTO;
use NFSePHP\DTO\ServicoDTO;
use PHPUnit\Framework\TestCase;

final class ServicoDTOTest extends TestCase
{
    public function test_construct_stores_properties(): void
    {
        $locPrest = new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105');
        $cServ = new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desenvolvimento');

        $dto = new ServicoDTO(locPrest: $locPrest, cServ: $cServ);

        self::assertSame($locPrest, $dto->locPrest);
        self::assertSame($cServ, $dto->cServ);
    }

    public function test_json_serialize_returns_all_properties(): void
    {
        $locPrest = new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105');
        $cServ = new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc');
        $dto = new ServicoDTO(locPrest: $locPrest, cServ: $cServ);

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('locPrest', $data);
        self::assertArrayHasKey('cServ', $data);
        self::assertSame($locPrest, $data['locPrest']);
    }

    public function test_to_string_returns_json(): void
    {
        $locPrest = new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105');
        $cServ = new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc');
        $dto = new ServicoDTO(locPrest: $locPrest, cServ: $cServ);

        self::assertJson((string) $dto);
    }
}
