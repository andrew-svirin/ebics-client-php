<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Services\CryptService;

use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass CryptService
 */
class GenerateNonceTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new CryptService();

        self::assertRegExp('/^[A-Z0-9]{32}$/', $sUT->generateNonce());
    }
}
