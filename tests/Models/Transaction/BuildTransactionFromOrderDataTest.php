<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models\Transaction;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
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

        $transaction = Transaction::buildTransactionFromOrderData($orderData);

        self::assertSame($orderData, $transaction->getOrderData());
    }

    public function failCase(): iterable
    {
        yield ['getId', 'id is null'];
        yield ['getPhase', 'phase is null'];
        yield ['getNumSegments', 'numSegments is null'];
        yield ['getSegmentNumber', 'segmentNumber is null'];
        yield ['getPlainOrderData', 'plainOrderData is null'];
    }

    /** @dataProvider failCase */
    public function testFailGetter(string $getter, string $message): void
    {
        $orderData = self::createMock(OrderData::class);

        $transaction = Transaction::buildTransactionFromOrderData($orderData);

        self::assertSame($orderData, $transaction->getOrderData());

        self::expectException(EbicsException::class);
        self::expectExceptionMessage($message);

        $transaction->$getter();
    }
}
