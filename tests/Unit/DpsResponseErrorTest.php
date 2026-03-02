<?php

namespace NFSePHP\Tests\Unit;

use NFSePHP\DTO\DpsResponseError;
use PHPUnit\Framework\TestCase;

final class DpsResponseErrorTest extends TestCase
{
    public function test_to_string_formats_codigo_and_descricao(): void
    {
        $error = new DpsResponseError('RNG6110', 'Falha Schema Xml');

        self::assertSame('RNG6110 - Falha Schema Xml', $error->__toString());
    }
}
