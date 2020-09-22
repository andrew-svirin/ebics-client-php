<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\ResponseHandler;

use AndrewSvirin\Ebics\Exceptions\EbicsResponseException;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ResponseHandler
 */
class CheckH004ReturnCodeTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <elem>
            <header xmlns="urn:org:ebics:H004">
                <mutable xmlns="urn:org:ebics:H004">
                    <ReturnCode xmlns="urn:org:ebics:H004">000000</ReturnCode>
                    <ReportText xmlns="urn:org:ebics:H004">hello</ReportText>
                </mutable>
            </header>
            <body xmlns="urn:org:ebics:H004">
                <ReturnCode xmlns="urn:org:ebics:H004">000000</ReturnCode>
            </body>
        </elem>
';

        $request  = new Request();
        $response = new Response($xml);

        $sUT->checkH004ReturnCode($request, $response);

        self::assertSame('fake assert', 'fake assert');
    }

    public function testNotFound(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <elem>
            <header xmlns="urn:org:ebics:H004">
                <mutable xmlns="urn:org:ebics:H004">
                    <ReturnCode xmlns="urn:org:ebics:H004">0</ReturnCode>
                    <ReportText xmlns="urn:org:ebics:H004">hello</ReportText>
                </mutable>
            </header>
            <body xmlns="urn:org:ebics:H004">
                <ReturnCode xmlns="urn:org:ebics:H004">hi!</ReturnCode>
            </body>
        </elem>
';

        $request  = new Request();
        $response = new Response($xml);

        self::expectException(EbicsResponseException::class);
        self::expectExceptionMessage('hello');

        $sUT->checkH004ReturnCode($request, $response);
    }
}
