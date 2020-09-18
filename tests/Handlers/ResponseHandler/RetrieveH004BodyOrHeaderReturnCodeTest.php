<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\ResponseHandler;

use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\OrderData;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * coversDefaultClass ResponseHandler
 */
class RetrieveH004BodyOrHeaderReturnCodeTest extends TestCase
{
    public function testReturnBody(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <elem>
            <header xmlns="urn:org:ebics:H004">
                <mutable xmlns="urn:org:ebics:H004">
                    <ReturnCode xmlns="urn:org:ebics:H004">000000</ReturnCode>
                </mutable>
            </header>
            <body xmlns="urn:org:ebics:H004">
                <ReturnCode xmlns="urn:org:ebics:H004">hi!</ReturnCode>
            </body>
        </elem>
';

        self::assertEquals('hi!', $sUT->retrieveH004BodyOrHeaderReturnCode(new OrderData($xml)));
    }

    public function testReturnHeader(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <elem>
            <header xmlns="urn:org:ebics:H004">
                <mutable xmlns="urn:org:ebics:H004">
                    <ReturnCode xmlns="urn:org:ebics:H004">000001</ReturnCode>
                </mutable>
            </header>
            <body xmlns="urn:org:ebics:H004">
                <ReturnCode xmlns="urn:org:ebics:H004">hi!</ReturnCode>
            </body>
        </elem>
';

        self::assertEquals('000001', $sUT->retrieveH004BodyOrHeaderReturnCode(new OrderData($xml)));
    }

    public function testNotFound(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
        </header>
';

        self::expectException(RuntimeException::class);
        $sUT->retrieveH004BodyOrHeaderReturnCode(new OrderData($xml));
    }
}
