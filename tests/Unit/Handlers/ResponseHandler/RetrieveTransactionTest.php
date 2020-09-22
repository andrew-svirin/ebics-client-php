<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\ResponseHandler;

use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass ResponseHandler
 */
class RetrieveTransactionTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
            <static xmlns="urn:org:ebics:H004">
                <TransactionID xmlns="urn:org:ebics:H004">TransactionID!</TransactionID>
                <NumSegments xmlns="urn:org:ebics:H004">10</NumSegments>
            </static>
            <mutable xmlns="urn:org:ebics:H004">
                <TransactionPhase xmlns="urn:org:ebics:H004">TransactionPhase!</TransactionPhase>
                <SegmentNumber xmlns="urn:org:ebics:H004">15</SegmentNumber>
            </mutable>
        </header>
';

        $actual = $sUT->retrieveTransaction(new Response($xml));

        self::assertSame('TransactionID!', $actual->getId());
        self::assertSame(10, $actual->getNumSegments());
        self::assertSame('TransactionPhase!', $actual->getPhase());
        self::assertSame(15, $actual->getSegmentNumber());
    }

    public function testOkWithoutInt(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
            <static xmlns="urn:org:ebics:H004">
                <TransactionID xmlns="urn:org:ebics:H004">TransactionID!</TransactionID>
                <NumSegments xmlns="urn:org:ebics:H004">a</NumSegments>
            </static>
            <mutable xmlns="urn:org:ebics:H004">
                <TransactionPhase xmlns="urn:org:ebics:H004">TransactionPhase!</TransactionPhase>
                <SegmentNumber xmlns="urn:org:ebics:H004">b</SegmentNumber>
            </mutable>
        </header>
';

        $actual = $sUT->retrieveTransaction(new Response($xml));

        self::assertSame('TransactionID!', $actual->getId());
        self::assertSame(0, $actual->getNumSegments());
        self::assertSame('TransactionPhase!', $actual->getPhase());
        self::assertSame(0, $actual->getSegmentNumber());
    }

    public function failXml(): iterable
    {
        yield [
            '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
            <static xmlns="urn:org:ebics:H004">
                <TransactionID xmlns="urn:org:ebics:H004">TransactionID!</TransactionID>
            </static>
            <mutable xmlns="urn:org:ebics:H004">
                <TransactionPhase xmlns="urn:org:ebics:H004">TransactionPhase!</TransactionPhase>
                <SegmentNumber xmlns="urn:org:ebics:H004">15</SegmentNumber>
            </mutable>
        </header>',
        ];

        yield [
            '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
            <static xmlns="urn:org:ebics:H004">
                <NumSegments xmlns="urn:org:ebics:H004">10</NumSegments>
            </static>
            <mutable xmlns="urn:org:ebics:H004">
                <TransactionPhase xmlns="urn:org:ebics:H004">TransactionPhase!</TransactionPhase>
                <SegmentNumber xmlns="urn:org:ebics:H004">15</SegmentNumber>
            </mutable>
        </header>',
        ];

        yield [
            '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
            <static xmlns="urn:org:ebics:H004">
                <NumSegments xmlns="urn:org:ebics:H004">10</NumSegments>
                <TransactionID xmlns="urn:org:ebics:H004">TransactionID!</TransactionID>
            </static>
            <mutable xmlns="urn:org:ebics:H004">
                <SegmentNumber xmlns="urn:org:ebics:H004">15</SegmentNumber>
            </mutable>
        </header>',
        ];

        yield [
            '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
            <static xmlns="urn:org:ebics:H004">
                <NumSegments xmlns="urn:org:ebics:H004">10</NumSegments>
                <TransactionID xmlns="urn:org:ebics:H004">TransactionID!</TransactionID>
            </static>
            <mutable xmlns="urn:org:ebics:H004">
                <TransactionPhase xmlns="urn:org:ebics:H004">TransactionPhase!</TransactionPhase>
            </mutable>
        </header>',
        ];

        yield [
            '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
            <static xmlns="urn:org:ebics:H004">
                <NumSegments xmlns="urn:org:ebics:H004">10</NumSegments>
                <TransactionID xmlns="urn:org:ebics:H004">TransactionID!</TransactionID>
            </static>
        </header>',
        ];

        yield [
            '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
            <mutable xmlns="urn:org:ebics:H004">
                <TransactionPhase xmlns="urn:org:ebics:H004">TransactionPhase!</TransactionPhase>
                <SegmentNumber xmlns="urn:org:ebics:H004">15</SegmentNumber>
            </mutable>
        </header>',
        ];
    }

    /** @dataProvider failXml */
    public function testWrongXml(string $xml): void
    {
        $sUT = new ResponseHandler();

        self::expectException(RuntimeException::class);
        $sUT->retrieveTransaction(new Response($xml));
    }
}
