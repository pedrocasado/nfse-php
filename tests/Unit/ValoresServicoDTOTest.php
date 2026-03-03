<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\ValoresServicoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ValoresServicoDTOTest extends TestCase
{
    public function test_construct_stores_required_fields(): void
    {
        $dto = new ValoresServicoDTO(
            vServ: '250.00',
            vLiq: '237.50',
        );

        self::assertSame('250.00', $dto->vServ);
        self::assertSame('237.50', $dto->vLiq);
        self::assertNull($dto->vBC);
        self::assertSame('0.00', $dto->vTotTribFed);
        self::assertSame('0.00', $dto->vTotTribEst);
        self::assertSame('0.00', $dto->vTotTribMun);
    }

    public function test_construct_with_optional_fields(): void
    {
        $dto = new ValoresServicoDTO(
            vServ: '250.00',
            vLiq: '237.50',
            vBC: '250.00',
            vDescIncond: '10.00',
            pAliqAplic: '5.00',
            vISSQN: '12.50',
        );

        self::assertSame('250.00', $dto->vBC);
        self::assertSame('10.00', $dto->vDescIncond);
        self::assertSame('5.00', $dto->pAliqAplic);
        self::assertSame('12.50', $dto->vISSQN);
    }

    public function test_json_serialize_returns_all_properties(): void
    {
        $dto = new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00');

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('vServ', $data);
        self::assertArrayHasKey('vLiq', $data);
        self::assertArrayHasKey('vTotTribFed', $data);
    }

    public function test_to_string_returns_json(): void
    {
        $dto = new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00');

        self::assertJson((string) $dto);
    }

    public function test_validation_fails_for_empty_v_serv(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new ValoresServicoDTO(vServ: '', vLiq: '100.00');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
