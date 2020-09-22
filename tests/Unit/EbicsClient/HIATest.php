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
class HIATest extends TestCase
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
        $certificateE       = self::createMock(Certificate::class);
        $certificateX       = self::createMock(Certificate::class);
        $date               = self::createMock(DateTime::class);

        $certificateFactory->expects(self::once())->method('generateCertificateEFromKeys')->with(['e'])->willReturn($certificateE);
        $certificateFactory->expects(self::once())->method('generateCertificateXFromKeys')->with(['x'])->willReturn($certificateX);

        $cryptService->expects(self::at(0))->method('generateKeys')->willReturn(['e']);
        $cryptService->expects(self::at(1))->method('generateKeys')->willReturn(['x']);
        $requestHandler->expects(self::once())->method('buildHIA')->with($bank, $user, $keyRing, $certificateE, $certificateX, $date)->willReturn($request);
        $requestMaker->expects(self::once())->method('post')->with($request, $bank)->willReturn($responseXml);
        $responseHandler->expects(self::once())->method('checkH004ReturnCode')->with($request, $responseXml)->willReturnArgument(1);
        $keyRing->expects(self::once())->method('setUserCertificateE')->with($certificateE);
        $keyRing->expects(self::once())->method('setUserCertificateX')->with($certificateX);

        $sUT = new EbicsClient(
            $requestMaker,
            $requestHandler,
            $responseHandler,
            $cryptService,
            $certificateFactory
        );

        self::assertEquals($responseXml, $sUT->HIA($bank, $user, $keyRing, $date));
    }
}
