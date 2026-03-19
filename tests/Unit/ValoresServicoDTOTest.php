<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\ValoresServicoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ValoresServicoDTOTest extends TestCase
{
    public function testConstructStoresRequiredFields(): void
    {
        $dto = new ValoresServicoDTO(
            vServ: '250.00',
            vLiq: '237.50',
        );

        self::assertSame('250.00', $dto->vServ);
        self::assertSame('237.50', $dto->vLiq);
        self::assertNull($dto->vBC);
        self::assertSame(null, $dto->vTotTribFed);
        self::assertSame(null, $dto->vTotTribEst);
        self::assertSame(null, $dto->vTotTribMun);
        self::assertNull($dto->pTotTribFed);
        self::assertNull($dto->pTotTribEst);
        self::assertNull($dto->pTotTribMun);
    }

    public function testConstructWithOptionalFields(): void
    {
        $dto = new ValoresServicoDTO(
            vServ: '250.00',
            vLiq: '237.50',
            vBC: '250.00',
            vDescIncond: '10.00',
            pAliqAplic: '5.00',
            vISSQN: '12.50',
            pTotTribFed: '8.50',
            pTotTribEst: '3.25',
            pTotTribMun: '1.10',
        );

        self::assertSame('250.00', $dto->vBC);
        self::assertSame('10.00', $dto->vDescIncond);
        self::assertSame('5.00', $dto->pAliqAplic);
        self::assertSame('12.50', $dto->vISSQN);
        self::assertSame('8.50', $dto->pTotTribFed);
        self::assertSame('3.25', $dto->pTotTribEst);
        self::assertSame('1.10', $dto->pTotTribMun);
    }

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $dto = new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00');

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('vServ', $data);
        self::assertArrayHasKey('vLiq', $data);
        self::assertArrayHasKey('vTotTribFed', $data);
        self::assertArrayHasKey('pTotTribFed', $data);
    }

    public function testToStringReturnsJson(): void
    {
        $dto = new ValoresServicoDTO(vServ: '100.00', vLiq: '95.00');

        self::assertJson((string) $dto);
    }

    public function testValidationFailsForEmptyVServ(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new ValoresServicoDTO(vServ: '', vLiq: '100.00');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
