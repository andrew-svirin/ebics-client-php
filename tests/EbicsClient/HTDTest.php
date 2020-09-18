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
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\Models\Transaction;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\RequestMaker;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * coversDefaultClass EbicsClient
 */
class HTDTest extends TestCase
{
    public function testOk(): void
    {
        $requestMaker     = self::createMock(RequestMaker::class);
        $requestHandler   = self::createMock(RequestHandler::class);
        $responseHandler  = self::createMock(ResponseHandler::class);
        $orderDataHandler = self::createMock(OrderDataHandler::class);

        $request            = new Request();
        $bank               = self::createMock(Bank::class);
        $responseXml        = self::createMock(Response::class);
        $certificateFactory = self::createMock(CertificateFactory::class);
        $cryptService       = self::createMock(CryptService::class);
        $keyRing            = self::createMock(KeyRing::class);
        $user               = self::createMock(User::class);
        $date               = self::createMock(DateTime::class);
        $orderDataEncrypted = self::createMock(OrderDataEncrypted::class);
        $orderData          = self::createMock(OrderData::class);
        $transaction        = self::createMock(Transaction::class);

        $requestHandler->expects(self::once())->method('buildHTD')->with($bank, $user, $keyRing, $date)->willReturn($request);
        $requestMaker->expects(self::once())->method('post')->with($request, $bank)->willReturn($responseXml);
        $responseHandler->expects(self::once())->method('checkH004ReturnCode')->with($request, $responseXml);
        $responseHandler->expects(self::once())->method('retrieveTransaction')->with($responseXml)->willReturn($transaction);
        $responseXml->expects(self::once())->method('addTransaction')->with($transaction);
        $responseHandler->expects(self::once())->method('retrieveOrderData')->with($responseXml)->willReturn($orderDataEncrypted);
        $cryptService->expects(self::once())->method('decryptOrderData')->with($keyRing, $orderDataEncrypted)->willReturn($orderData);
        $transaction->expects(self::once())->method('setOrderData')->with($orderData);

        $sUT = new EbicsClient(
            $requestMaker,
            $requestHandler,
            $responseHandler,
            $cryptService,
            $certificateFactory,
            $orderDataHandler
        );

        self::assertEquals($responseXml, $sUT->HTD($bank, $user, $keyRing, $date));
    }
}
