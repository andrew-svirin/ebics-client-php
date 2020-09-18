<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Factories\TransactionFactory;

use AndrewSvirin\Ebics\Factories\TransactionFactory;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\Transaction;
use PHPStan\Testing\TestCase;

/**
 * coversDefaultClass TransactionFactory
 */
class BuildTransactionFromOrderDataTest extends TestCase
{
    public function testOk(): void
    {
        $orderData = self::createMock(OrderData::class);

        $transaction = new Transaction();
        $transaction->setOrderData($orderData);

        self::assertEquals($transaction, TransactionFactory::buildTransactionFromOrderData($orderData));
    }
}
