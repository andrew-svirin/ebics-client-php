<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use PHPUnit\Framework\TestCase;

class KeyRingTest extends TestCase
{
    public function testEmptyPassword(): void
    {
        $sUT = new KeyRing();

        self::expectException(EbicsException::class);
        self::expectExceptionMessage('Password must be set');

        $sUT->getPassword();
    }

    public function testVersion(): void
    {
        $sUT = new KeyRing();

        self::assertSame('A006', $sUT->getUserCertificateAVersion());
        self::assertSame('X002', $sUT->getUserCertificateXVersion());
        self::assertSame('E002', $sUT->getUserCertificateEVersion());
        self::assertSame('X002', $sUT->getBankCertificateXVersion());
        self::assertSame('E002', $sUT->getBankCertificateEVersion());
    }
}
