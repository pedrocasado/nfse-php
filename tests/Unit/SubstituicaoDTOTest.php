<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\SubstituicaoDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Tests for SubstituicaoDTO (TCSubstituicao from DPS_v1.01.xsd).
 * Dados da NFS-e a ser substituída.
 */
final class SubstituicaoDTOTest extends TestCase
{
    public function testConstructStoresRequiredFields(): void
    {
        $dto = new SubstituicaoDTO(
            chSubstda: '33045572210738989000199000000000001026029316934590',
            cMotivo: '01',
        );

        self::assertSame('33045572210738989000199000000000001026029316934590', $dto->chSubstda);
        self::assertSame('01', $dto->cMotivo);
        self::assertNull($dto->xMotivo);
    }

    public function testConstructWithXMotivo(): void
    {
        $dto = new SubstituicaoDTO(
            chSubstda: '33045572210738989000199000000000001026029316934590',
            cMotivo: '99',
            xMotivo: 'Desenquadramento de NFS-e do Simples Nacional - descrição detalhada',
        );

        self::assertSame('99', $dto->cMotivo);
        self::assertSame('Desenquadramento de NFS-e do Simples Nacional - descrição detalhada', $dto->xMotivo);
    }

    public function testJsonSerializeReturnsAllProperties(): void
    {
        $dto = new SubstituicaoDTO(
            chSubstda: '33045572210738989000199000000000001026029316934590',
            cMotivo: '02',
        );

        $data = $dto->jsonSerialize();

        self::assertIsArray($data);
        self::assertArrayHasKey('chSubstda', $data);
        self::assertArrayHasKey('cMotivo', $data);
        self::assertArrayHasKey('xMotivo', $data);
    }

    public function testValidationFailsForInvalidCMotivo(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new SubstituicaoDTO(
            chSubstda: '33045572210738989000199000000000001026029316934590',
            cMotivo: '00',
        );

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForInvalidChaveLength(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $dto = new SubstituicaoDTO(
            chSubstda: '123',
            cMotivo: '01',
        );

        $violations = $validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
