<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models;

use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\Models\Transaction;
use PHPUnit\Framework\TestCase;

use function trim;

class ResponseTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new Response();

        self::assertEquals('<?xml version="1.0" encoding="utf-8"?>', trim($sUT->saveXML()));
        self::assertCount(0, $sUT->getTransactions());
        self::assertNull($sUT->getLastTransaction());

        $transaction1 = self::createMock(Transaction::class);
        $transaction2 = self::createMock(Transaction::class);

        $sUT->addTransaction($transaction1);

        self::assertCount(1, $sUT->getTransactions());
        self::assertSame($transaction1, $sUT->getLastTransaction());

        $sUT->addTransaction($transaction2);

        self::assertCount(2, $sUT->getTransactions());
        self::assertSame($transaction2, $sUT->getLastTransaction());

        $sUT = new Response('<?xml version="1.0" encoding="utf-8"?><toto></toto>');

        self::assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="utf-8"?><toto></toto>', trim($sUT->saveXML()));
    }
}
