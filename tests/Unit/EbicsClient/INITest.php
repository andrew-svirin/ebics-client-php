<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\EbicsClient;

use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Factories\CertificateFactory;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\RequestMaker;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass EbicsClient
 */
class INITest extends TestCase
{
    public function testOk(): void
    {
        $requestMaker    = self::createMock(RequestMaker::class);
        $requestHandler  = self::createMock(RequestHandler::class);
        $responseHandler = self::createMock(ResponseHandler::class);

        $request            = new Request();
        $bank               = self::createMock(Bank::class);
        $responseXml        = self::createMock(Response::class);
        $certificateFactory = self::createMock(CertificateFactory::class);
        $cryptService       = self::createMock(CryptService::class);
        $keyRing            = self::createMock(KeyRing::class);
        $user               = self::createMock(User::class);
        $certificate        = self::createMock(Certificate::class);
        $date               = self::createMock(DateTime::class);

        $certificateFactory->expects(self::once())->method('generateCertificateAFromKeys')->willReturn($certificate);
        $cryptService->expects(self::once())->method('generateKeys')->willReturn([]);
        $requestHandler->expects(self::once())->method('buildINI')->with($bank, $user, $keyRing, $certificate, $date)->willReturn($request);
        $requestMaker->expects(self::once())->method('post')->with($request, $bank)->willReturn($responseXml);
        $keyRing->expects(self::once())->method('setUserCertificateA')->with($certificate);
        $responseHandler->expects(self::once())->method('checkH004ReturnCode')->with($request, $responseXml)->willReturnArgument(1);

        $sUT = new EbicsClient(
            $requestMaker,
            $requestHandler,
            $responseHandler,
            $cryptService,
            $certificateFactory
        );

        self::assertEquals($responseXml, $sUT->INI($bank, $user, $keyRing, $date));
    }
}
