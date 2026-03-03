<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\CodigoServicoDTO;
use NFSePHP\DTO\InfDpsDTO;
use NFSePHP\DTO\LocalPrestacaoDTO;
use NFSePHP\DTO\PrestadorDTO;
use NFSePHP\DTO\RegimeTributarioDTO;
use NFSePHP\DTO\ServicoDTO;
use NFSePHP\DTO\ValoresServicoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class InfDpsDTOTest extends TestCase
{
    private function createMinimalInfDpsDTO(): InfDpsDTO
    {
        return new InfDpsDTO(
            tpAmb: '2',
            dhEmi: '2026-03-03T10:00:00-03:00',
            serie: '1',
            nDPS: '1',
            dCompet: '2026-03-01',
            tpEmit: '1',
            cLocEmi: '3304557',
            prest: new PrestadorDTO(regTrib: new RegimeTributarioDTO(), cnpj: '38744743000149'),
            serv: new ServicoDTO(
                locPrest: new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105'),
                cServ: new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desenvolvimento'),
            ),
            valores: new ValoresServicoDTO(vServ: '250.00', vLiq: '237.50'),
        );
    }

    public function testConstructStoresRequiredFields(): void
    {
        $dto = $this->createMinimalInfDpsDTO();

        self::assertSame('2', $dto->tpAmb);
        self::assertSame('2026-03-03T10:00:00-03:00', $dto->dhEmi);
        self::assertSame('1', $dto->serie);
        self::assertSame('1', $dto->nDPS);
        self::assertSame('2026-03-01', $dto->dCompet);
        self::assertSame('1', $dto->tpEmit);
        self::assertSame('3304557', $dto->cLocEmi);
        self::assertNull($dto->toma);
        self::assertSame('1.01', $dto->versao);
        self::assertSame('1.00', $dto->verAplic);
    }

    public function testConstructWithTomador(): void
    {
        $tomador = new \NFSePHP\DTO\TomadorDTO(xNome: 'Cliente', cnpj: '12345678000199');
        $dto = new InfDpsDTO(
            tpAmb: '2',
            dhEmi: '2026-03-03T10:00:00-03:00',
            serie: '1',
            nDPS: '1',
            dCompet: '2026-03-01',
            tpEmit: '1',
            cLocEmi: '3304557',
            prest: new PrestadorDTO(regTrib: new RegimeTributarioDTO(), cnpj: '38744743000149'),
            serv: new ServicoDTO(
                locPrest: new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105'),
                cServ: new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc'),
            ),
            valores: new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00'),
            toma: $tomador,
        );

        self::assertSame($tomador, $dto->toma);
    }

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $dto = $this->createMinimalInfDpsDTO();

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('tpAmb', $data);
        self::assertArrayHasKey('prest', $data);
        self::assertArrayHasKey('serv', $data);
        self::assertArrayHasKey('valores', $data);
    }

    public function testToJsonReturnsJsonString(): void
    {
        $dto = $this->createMinimalInfDpsDTO();

        self::assertJson($dto->toJson());
    }

    public function testToStringReturnsJson(): void
    {
        $dto = $this->createMinimalInfDpsDTO();

        self::assertJson((string) $dto);
    }

    public function testValidationFailsForInvalidTpAmb(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new InfDpsDTO(
            tpAmb: '9',
            dhEmi: '2026-03-03T10:00:00-03:00',
            serie: '1',
            nDPS: '1',
            dCompet: '2026-03-01',
            tpEmit: '1',
            cLocEmi: '3304557',
            prest: new PrestadorDTO(regTrib: new RegimeTributarioDTO(), cnpj: '38744743000149'),
            serv: new ServicoDTO(
                locPrest: new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105'),
                cServ: new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc'),
            ),
            valores: new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00'),
        );

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForInvalidDCompetFormat(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new InfDpsDTO(
            tpAmb: '2',
            dhEmi: '2026-03-03T10:00:00-03:00',
            serie: '1',
            nDPS: '1',
            dCompet: '03-01-2026',
            tpEmit: '1',
            cLocEmi: '3304557',
            prest: new PrestadorDTO(regTrib: new RegimeTributarioDTO(), cnpj: '38744743000149'),
            serv: new ServicoDTO(
                locPrest: new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105'),
                cServ: new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc'),
            ),
            valores: new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00'),
        );

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testConstructWithOptionalFields(): void
    {
        $dto = new InfDpsDTO(
            tpAmb: '2',
            dhEmi: '2026-03-03T10:00:00-03:00',
            serie: '1',
            nDPS: '1',
            dCompet: '2026-03-01',
            tpEmit: '1',
            cLocEmi: '3304557',
            prest: new PrestadorDTO(regTrib: new RegimeTributarioDTO(), cnpj: '38744743000149'),
            serv: new ServicoDTO(
                locPrest: new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105'),
                cServ: new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc'),
            ),
            valores: new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00'),
            cMotivoEmisTI: '001',
            chNFSeRej: 'chave_rejeicao',
        );

        self::assertSame('001', $dto->cMotivoEmisTI);
        self::assertSame('chave_rejeicao', $dto->chNFSeRej);
    }

    public function testConstructWithSubstituicao(): void
    {
        $subst = new \NFSePHP\DTO\SubstituicaoDTO(
            chSubstda: '33045572210738989000199000000000001026029316934590',
            cMotivo: '01',
        );
        $dto = new InfDpsDTO(
            tpAmb: '2',
            dhEmi: '2026-03-03T10:00:00-03:00',
            serie: '1',
            nDPS: '1',
            dCompet: '2026-03-01',
            tpEmit: '1',
            cLocEmi: '3304557',
            prest: new PrestadorDTO(regTrib: new RegimeTributarioDTO(), cnpj: '38744743000149'),
            serv: new ServicoDTO(
                locPrest: new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105'),
                cServ: new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc'),
            ),
            valores: new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00'),
            subst: $subst,
        );

        self::assertSame($subst, $dto->subst);
    }

    public function testConstructWithIntermediario(): void
    {
        $interm = new \NFSePHP\DTO\IntermediarioDTO(xNome: 'Intermediária LTDA', cnpj: '12345678000199');
        $dto = new InfDpsDTO(
            tpAmb: '2',
            dhEmi: '2026-03-03T10:00:00-03:00',
            serie: '1',
            nDPS: '1',
            dCompet: '2026-03-01',
            tpEmit: '3',
            cLocEmi: '3304557',
            prest: new PrestadorDTO(regTrib: new RegimeTributarioDTO(), cnpj: '38744743000149'),
            serv: new ServicoDTO(
                locPrest: new LocalPrestacaoDTO(cLocPrestacao: '3304557', cPaisPrestacao: '105'),
                cServ: new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc'),
            ),
            valores: new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00'),
            interm: $interm,
        );

        self::assertSame($interm, $dto->interm);
    }
}
