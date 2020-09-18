<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models;

use AndrewSvirin\Ebics\Models\Bank;
use PHPUnit\Framework\TestCase;

class BankTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new Bank('test', 'test2', false);

        self::assertSame('test', $sUT->getHostId());
        self::assertSame('test2', $sUT->getUrl());
        self::assertFalse($sUT->isCertified());

        $sUT = new Bank('test', 'test2', true);

        self::assertSame('test', $sUT->getHostId());
        self::assertSame('test2', $sUT->getUrl());
        self::assertTrue($sUT->isCertified());
    }
}
