<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models;

use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new Transaction();

        $orderData = self::createMock(OrderData::class);

        $sUT->setId('test1');
        $sUT->setPhase('test2');
        $sUT->setSegmentNumber(20);
        $sUT->setNumSegments(10);
        $sUT->setPlainOrderData('test3');
        $sUT->setOrderData($orderData);

        self::assertSame('test1', $sUT->getId());
        self::assertSame('test2', $sUT->getPhase());
        self::assertSame(20, $sUT->getSegmentNumber());
        self::assertSame(10, $sUT->getNumSegments());
        self::assertSame('test3', $sUT->getPlainOrderData());
        self::assertSame($orderData, $sUT->getOrderData());
    }
}
