<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\EbicsClient;

use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Exceptions\DownloadPostprocessDoneException;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\CertificateFactory;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\Models\Transaction;
use AndrewSvirin\Ebics\RequestMaker;
use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

/**
 * coversDefaultClass EbicsClient
 */
class TransferReceiptTest extends TestCase
{
    public function testFail(): void
    {
        $requestMaker       = self::createMock(RequestMaker::class);
        $requestHandler     = self::createMock(RequestHandler::class);
        $responseHandler    = self::createMock(ResponseHandler::class);
        $certificateFactory = self::createMock(CertificateFactory::class);
        $cryptService       = self::createMock(CryptService::class);

        $bank        = self::createMock(Bank::class);
        $responseXml = self::createMock(Response::class);
        $keyRing     = self::createMock(KeyRing::class);

        $responseXml->expects(self::once())->method('getLastTransaction')->willReturn(null);

        $sUT = new EbicsClient(
            $requestMaker,
            $requestHandler,
            $responseHandler,
            $cryptService,
            $certificateFactory
        );

        self::expectException(EbicsException::class);
        self::expectExceptionMessage('There is no transactions to mark as received');

        $sUT->transferReceipt($bank, $keyRing, $responseXml);
    }

    public function testOk(): void
    {
        $requestMaker       = self::createMock(RequestMaker::class);
        $requestHandler     = self::createMock(RequestHandler::class);
        $responseHandler    = self::createMock(ResponseHandler::class);
        $cryptService       = self::createMock(CryptService::class);
        $certificateFactory = self::createMock(CertificateFactory::class);

        $request     = new Request();
        $bank        = self::createMock(Bank::class);
        $responseXml = self::createMock(Response::class);
        $keyRing     = self::createMock(KeyRing::class);
        $transaction = self::createMock(Transaction::class);

        $responseXml->expects(self::once())->method('getLastTransaction')->willReturn($transaction);
        $requestHandler->expects(self::once())->method('buildTransferReceipt')->with($bank, $keyRing, $transaction, true)->willReturn($request);
        $requestMaker->expects(self::once())->method('post')->with($request, $bank)->willReturn($responseXml);
        $responseHandler->expects(self::once())->method('checkH004ReturnCode')->with($request, $responseXml);

        $sUT = new EbicsClient(
            $requestMaker,
            $requestHandler,
            $responseHandler,
            $cryptService,
            $certificateFactory
        );

        self::assertEquals($responseXml, $sUT->transferReceipt($bank, $keyRing, $responseXml));
    }

    public function testThrowException(): void
    {
        $requestMaker       = self::createMock(RequestMaker::class);
        $requestHandler     = self::createMock(RequestHandler::class);
        $responseHandler    = self::createMock(ResponseHandler::class);
        $certificateFactory = self::createMock(CertificateFactory::class);
        $cryptService       = self::createMock(CryptService::class);

        $request     = new Request();
        $bank        = self::createMock(Bank::class);
        $responseXml = self::createMock(Response::class);
        $keyRing     = self::createMock(KeyRing::class);
        $transaction = self::createMock(Transaction::class);

        $responseXml->expects(self::once())->method('getLastTransaction')->willReturn($transaction);
        $requestHandler->expects(self::once())->method('buildTransferReceipt')->with($bank, $keyRing, $transaction, true)->willReturn($request);
        $requestMaker->expects(self::once())->method('post')->with($request, $bank)->willReturn($responseXml);
        $responseHandler->expects(self::once())->method('checkH004ReturnCode')->with($request, $responseXml)->willThrowException(new DownloadPostprocessDoneException());

        $sUT = new EbicsClient(
            $requestMaker,
            $requestHandler,
            $responseHandler,
            $cryptService,
            $certificateFactory
        );

        self::assertEquals($responseXml, $sUT->transferReceipt($bank, $keyRing, $responseXml));
    }
}
