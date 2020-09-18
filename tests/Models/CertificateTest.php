<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models;

use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\CertificateX509;
use PHPUnit\Framework\TestCase;

class CertificateTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new Certificate('test', 'test2');

        self::assertSame('test', $sUT->getType());
        self::assertSame('test2', $sUT->getPublicKey());
        self::assertNull($sUT->getPrivateKey());
        self::assertNull($sUT->getContent());
        self::assertNull($sUT->toX509());

        $sUT = new Certificate('test', 'test2', 'test3');

        self::assertSame('test', $sUT->getType());
        self::assertSame('test2', $sUT->getPublicKey());
        self::assertSame('test3', $sUT->getPrivateKey());
        self::assertNull($sUT->getContent());
        self::assertNull($sUT->toX509());

        $sUT = new Certificate('test', 'test2', 'test3', 'test4');

        self::assertSame('test', $sUT->getType());
        self::assertSame('test2', $sUT->getPublicKey());
        self::assertSame('test3', $sUT->getPrivateKey());
        self::assertSame('test4', $sUT->getContent());

        $certificat = new CertificateX509();
        $certificat->loadX509('test4');

        self::assertEquals($certificat, $sUT->toX509());
    }
}
