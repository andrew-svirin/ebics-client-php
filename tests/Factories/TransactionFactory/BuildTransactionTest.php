<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Factories\TransactionFactory;

use AndrewSvirin\Ebics\Factories\TransactionFactory;
use AndrewSvirin\Ebics\Models\Transaction;
use PHPStan\Testing\TestCase;

/**
 * coversDefaultClass TransactionFactory
 */
class BuildTransactionTest extends TestCase
{
    public function testOk(): void
    {
        $transaction = new Transaction();
        $transaction->setId('test');
        $transaction->setPhase('test2');
        $transaction->setNumSegments(3);
        $transaction->setSegmentNumber(4);

        self::assertEquals($transaction, TransactionFactory::buildTransaction('test', 'test2', '3', '4'));

        $transaction = TransactionFactory::buildTransaction('test', 'test2', '3', '4');

        self::assertSame('test', $transaction->getId());
        self::assertSame('test2', $transaction->getPhase());
        self::assertSame(3, $transaction->getNumSegments());
        self::assertSame(4, $transaction->getSegmentNumber());
    }
}
