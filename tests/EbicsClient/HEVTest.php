<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\EbicsClient;

use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\RequestMaker;
use PHPUnit\Framework\TestCase;

/**
 * coversDefaultClass EbicsClient
 */
class HEVTest extends TestCase
{
    public function testOk(): void
    {
        $requestMaker    = self::createMock(RequestMaker::class);
        $requestHandler  = self::createMock(RequestHandler::class);
        $responseHandler = self::createMock(ResponseHandler::class);

        $request     = new Request();
        $bank        = self::createMock(Bank::class);
        $responseXml = self::createMock(Response::class);

        $requestHandler->expects(self::once())->method('buildHEV')->willReturn($request);
        $requestMaker->expects(self::once())->method('post')->with($request, $bank)->willReturn($responseXml);
        $responseHandler->expects(self::once())->method('checkH000ReturnCode')->with($request, $responseXml)->willReturnArgument(1);

        $sUT = new EbicsClient(
            $requestMaker,
            $requestHandler,
            $responseHandler
        );

        self::assertEquals($responseXml, $sUT->HEV($bank));
    }
}
