<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models;

use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use PHPUnit\Framework\TestCase;

class OrderDataEncryptedTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new OrderDataEncrypted('test', 'test2');

        self::assertSame('test', $sUT->getOrderData());
        self::assertSame('test2', $sUT->getTransactionKey());
    }
}
