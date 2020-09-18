<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Services\CryptService;

use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

use function ctype_print;

class CalculateHashTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new CryptService();

        $result = $sUT->calculateHash('test');

        self::assertIsString($result);
        self::assertFalse(ctype_print($result)); // binary
    }
}
