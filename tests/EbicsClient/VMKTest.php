<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\EbicsClient;

use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Factories\CertificateFactory;
use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\RequestMaker;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * coversDefaultClass EbicsClient
 */
class VMKTest extends TestCase
{
    public function testOk(): void
    {
        $requestMaker       = self::createMock(RequestMaker::class);
        $requestHandler     = self::createMock(RequestHandler::class);
        $responseHandler    = self::createMock(ResponseHandler::class);
        $orderDataHandler   = self::createMock(OrderDataHandler::class);
        $certificateFactory = self::createMock(CertificateFactory::class);
        $cryptService       = self::createMock(CryptService::class);

        $request     = new Request();
        $bank        = self::createMock(Bank::class);
        $responseXml = self::createMock(Response::class);
        $keyRing     = self::createMock(KeyRing::class);
        $user        = self::createMock(User::class);
        $date        = self::createMock(DateTime::class);

        $requestHandler->expects(self::once())->method('buildVMK')->with($bank, $user, $keyRing, $date, null, null)->willReturn($request);
        $requestMaker->expects(self::once())->method('post')->with($request, $bank)->willReturn($responseXml);
        $responseHandler->expects(self::once())->method('checkH004ReturnCode')->with($request, $responseXml);

        $sUT = new EbicsClient(
            $requestMaker,
            $requestHandler,
            $responseHandler,
            $cryptService,
            $certificateFactory,
            $orderDataHandler
        );

        self::assertEquals($responseXml, $sUT->VMK($bank, $user, $keyRing, $date));
    }
}
