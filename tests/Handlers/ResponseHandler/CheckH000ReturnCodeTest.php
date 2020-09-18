<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\ResponseHandler;

use AndrewSvirin\Ebics\Exceptions\EbicsResponseException;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use PHPUnit\Framework\TestCase;

/**
 * coversDefaultClass ResponseHandler
 */
class CheckH000ReturnCodeTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <SystemReturnCode xmlns="http://www.ebics.org/H000">
            <ReturnCode xmlns="http://www.ebics.org/H000">000000</ReturnCode>
        </SystemReturnCode>
';

        $request  = new Request();
        $response = new Response($xml);

        self::assertSame($response, $sUT->checkH000ReturnCode($request, $response));
    }

    public function testNotFound(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <SystemReturnCode xmlns="http://www.ebics.org/H000">
            <ReturnCode xmlns="http://www.ebics.org/H000">0</ReturnCode>
            <ReportText xmlns="http://www.ebics.org/H000">hi!</ReportText>
        </SystemReturnCode>
';

        $request  = new Request();
        $response = new Response($xml);

        self::expectException(EbicsResponseException::class);
        self::expectExceptionMessage('hi!');

        $sUT->checkH000ReturnCode($request, $response);
    }
}
