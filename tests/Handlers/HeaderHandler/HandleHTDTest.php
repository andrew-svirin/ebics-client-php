<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\HeaderHandler;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\HeaderHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\DOMDocument;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use PHPUnit\Framework\TestCase;

class HandleHTDTest extends TestCase
{
    public function testEmptyBankCertificateX(): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyRing = self::createMock(KeyRing::class);

        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');
        $user->expects(self::any())->method('getPartnerId')->willReturn('myPartnerId');
        $user->expects(self::any())->method('getUserId')->willReturn('myUserId');

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');

        $sUT = new HeaderHandler($cryptService);

        $domDocument = new DOMDocument();
        $domElement  = $domDocument->createElement('test');
        $domDocument->appendChild($domElement);

        self::expectException(EbicsException::class);
        self::expectExceptionMessage('Certificate X is empty.');

        $sUT->handleHTD($bank, $user, $keyRing, $domDocument, $domElement, new DateTime('2010-10-10 10:10:10'));
    }

    public function testEmptyBankCertificateE(): void
    {
        $bank        = self::createMock(Bank::class);
        $user        = self::createMock(User::class);
        $keyRing     = self::createMock(KeyRing::class);
        $certificatX = self::createMock(Certificate::class);

        $keyRing->expects(self::once())->method('getBankCertificateX')->willReturn($certificatX);
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');
        $user->expects(self::any())->method('getPartnerId')->willReturn('myPartnerId');
        $user->expects(self::any())->method('getUserId')->willReturn('myUserId');

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');

        $sUT = new HeaderHandler($cryptService);

        $domDocument = new DOMDocument();
        $domElement  = $domDocument->createElement('test');
        $domDocument->appendChild($domElement);

        self::expectException(EbicsException::class);
        self::expectExceptionMessage('Certificate E is empty.');

        $sUT->handleHTD($bank, $user, $keyRing, $domDocument, $domElement, new DateTime('2010-10-10 10:10:10'));
    }

    public function testOk(): void
    {
        $bank        = self::createMock(Bank::class);
        $user        = self::createMock(User::class);
        $keyRing     = self::createMock(KeyRing::class);
        $certificatX = self::createMock(Certificate::class);
        $certificatE = self::createMock(Certificate::class);

        $keyRing->expects(self::once())->method('getBankCertificateX')->willReturn($certificatX);
        $keyRing->expects(self::once())->method('getBankCertificateE')->willReturn($certificatE);
        $keyRing->expects(self::once())->method('getBankCertificateXVersion')->willReturn('certX');
        $keyRing->expects(self::once())->method('getBankCertificateEVersion')->willReturn('certE');
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');
        $user->expects(self::any())->method('getPartnerId')->willReturn('myPartnerId');
        $user->expects(self::any())->method('getUserId')->willReturn('myUserId');

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::exactly(2))->method('calculateDigest')->willReturn('myDigest');
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');

        $sUT = new HeaderHandler($cryptService);

        $expected = '<?xml version="1.0" encoding="utf-8"?>
<test>
    <header authenticate="true">
        <static>
            <HostID>myHostId</HostID>
            <Nonce>myNonce</Nonce>
            <Timestamp>2010-10-10T10:10:10Z</Timestamp>
            <PartnerID>myPartnerId</PartnerID>
            <UserID>myUserId</UserID>
            <Product Language="de">Ebics client PHP</Product>
            <OrderDetails>
                <OrderType>HTD</OrderType>
                <OrderAttribute>DZHNN</OrderAttribute>
                <StandardOrderParams/>
            </OrderDetails>
           <BankPubKeyDigests>
               <Authentication Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="certX">bXlEaWdlc3Q=</Authentication>
               <Encryption Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="certE">bXlEaWdlc3Q=</Encryption>
           </BankPubKeyDigests>

            <SecurityMedium>0000</SecurityMedium>
        </static>
        <mutable>
          <TransactionPhase>Initialisation</TransactionPhase>
        </mutable>
    </header>
</test>
        ';

        $domDocument = new DOMDocument();
        $domElement  = $domDocument->createElement('test');
        $domDocument->appendChild($domElement);

        self::assertXmlStringEqualsXmlString($expected, $sUT->handleHTD($bank, $user, $keyRing, $domDocument, $domElement, new DateTime('2010-10-10 10:10:10')));
    }
}
