<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Models\Transaction;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\Transaction;
use PHPStan\Testing\TestCase;

/**
 * @coversDefaultClass TransactionFactory
 */
class BuildTransactionTest extends TestCase
{
    public function testOk(): void
    {
        $transaction = Transaction::buildTransaction('test', 'test2', 3, 4);

        self::assertSame('test', $transaction->getId());
        self::assertSame('test2', $transaction->getPhase());
        self::assertSame(3, $transaction->getNumSegments());
        self::assertSame(4, $transaction->getSegmentNumber());
    }

    public function failCase(): iterable
    {
        yield ['getOrderData', 'orderData is null'];
        yield ['getPlainOrderData', 'plainOrderData is null'];
    }

    /** @dataProvider failCase */
    public function testFailGetter(string $getter, string $message): void
    {
        $orderData = self::createMock(OrderData::class);

        $transaction = Transaction::buildTransaction('test', 'test2', 3, 4);

        self::assertSame('test', $transaction->getId());
        self::assertSame('test2', $transaction->getPhase());
        self::assertSame(3, $transaction->getNumSegments());
        self::assertSame(4, $transaction->getSegmentNumber());

        self::expectException(EbicsException::class);
        self::expectExceptionMessage($message);

        $transaction->$getter();
    }
}
