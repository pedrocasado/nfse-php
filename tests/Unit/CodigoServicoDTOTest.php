<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\CodigoServicoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class CodigoServicoDTOTest extends TestCase
{
    public function testConstructStoresRequiredFields(): void
    {
        $dto = new CodigoServicoDTO(
            cTribNac: '1.01',
            xDescServ: 'Desenvolvimento de software',
        );

        self::assertSame('1.01', $dto->cTribNac);
        self::assertSame('Desenvolvimento de software', $dto->xDescServ);
        self::assertNull($dto->cNBS);
        self::assertNull($dto->cTribMun);
        self::assertNull($dto->cIntContrib);
    }

    public function testConstructWithOptionalFields(): void
    {
        $dto = new CodigoServicoDTO(
            cTribNac: '1.01',
            xDescServ: 'Serviço',
            cNBS: '6201501',
            cTribMun: '1.01.001',
            cIntContrib: 'INT001',
        );

        self::assertSame('6201501', $dto->cNBS);
        self::assertSame('1.01.001', $dto->cTribMun);
        self::assertSame('INT001', $dto->cIntContrib);
    }

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $dto = new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc');

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('cTribNac', $data);
        self::assertArrayHasKey('xDescServ', $data);
    }

    public function testToStringReturnsJson(): void
    {
        $dto = new CodigoServicoDTO(cTribNac: '1.01', xDescServ: 'Desc');

        self::assertJson((string) $dto);
    }

    public function testValidationFailsForEmptyCTribNac(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new CodigoServicoDTO(cTribNac: '', xDescServ: 'Desc');

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
