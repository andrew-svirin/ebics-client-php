<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\RequestHandler;

use AndrewSvirin\Ebics\Handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\Handlers\HeaderHandler;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use PHPUnit\Framework\TestCase;

class BuildFDLTest extends TestCase
{
    public function testOk(): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyring = self::createMock(KeyRing::class);

        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $rsaPublicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWidncNpkqmHnFZbicgeZfmRht
/+TzVO9RtZQ7NDHPWvWYih3LBMsBKfX9rSKeso+c+feDLge5+Tp9vKt3Ip1vnaBr
48jfAvkmzQyGk6OAMk2HTXY7rOZls3Cv5jhuR95h+pO6AVCloN6wq4+Y5PnyyX7Z
A3jkP/yhA0WITVryywIDAQAB
-----END PUBLIC KEY-----';

        $certificatX = self::createMock(Certificate::class);

        $certificatX->expects(self::never())->method('getPrivateKey');
        $certificatX->expects(self::exactly(2))->method('getPublicKey')->willReturn($rsaPublicKey);
        $keyring->expects(self::once())->method('getBankCertificateX')->willReturn($certificatX);
        $keyring->expects(self::once())->method('getBankCertificateE')->willReturn($certificatX);
        $keyring->expects(self::never())->method('getUserCertificateX');
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');
        $cryptService->expects(self::exactly(2))->method('calculateHash')->willReturn('myNonce');
        $cryptService->expects(self::once())->method('cryptSignatureValue')->willReturn('myNonce');

        $headerHandler        = new HeaderHandler($cryptService);
        $authSignatureHandler = new AuthSignatureHandler($cryptService);

        $sUT = new RequestHandler(null, $headerHandler, null, null, $authSignatureHandler);

        $date     = new DateTime('2010-10-10 10:10:10');
        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<ebicsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Version="H004" Revision="1">
   <header authenticate="true">
      <static>
         <HostID>myHostId</HostID>
         <Nonce>myNonce</Nonce>
         <Timestamp>2010-10-10T10:10:10Z</Timestamp>
         <PartnerID />
         <UserID />
         <Product Language="de">Ebics client PHP</Product>
         <OrderDetails>
            <OrderType>FDL</OrderType>
            <OrderAttribute>DZHNN</OrderAttribute>
            <FDLOrderParams>
               <FileFormat CountryCode="test">test</FileFormat>
            </FDLOrderParams>
         </OrderDetails>
         <BankPubKeyDigests>
            <Authentication Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">xQR4tHm5y6NBc3o3hEhZbCN9FJM8Tygw/EvHpzFo7kc=</Authentication>
            <Encryption Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">xQR4tHm5y6NBc3o3hEhZbCN9FJM8Tygw/EvHpzFo7kc=</Encryption>
         </BankPubKeyDigests>
         <SecurityMedium>0000</SecurityMedium>
      </static>
      <mutable>
         <TransactionPhase>Initialisation</TransactionPhase>
      </mutable>
   </header>
   <AuthSignature>
      <ds:SignedInfo>
         <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
         <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />
         <ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])">
            <ds:Transforms>
               <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />
            <ds:DigestValue>bXlOb25jZQ==</ds:DigestValue>
         </ds:Reference>
      </ds:SignedInfo>
      <ds:SignatureValue>bXlOb25jZQ==</ds:SignatureValue>
   </AuthSignature>
   <body />
</ebicsRequest>

';

        self::assertXmlStringEqualsXmlString($expected, $sUT->buildFDL($bank, $user, $keyring, $date, 'test', 'test')->getContent());
    }

    public function testOkWithStartDateAndEndDate(): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyring = self::createMock(KeyRing::class);

        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $rsaPublicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWidncNpkqmHnFZbicgeZfmRht
/+TzVO9RtZQ7NDHPWvWYih3LBMsBKfX9rSKeso+c+feDLge5+Tp9vKt3Ip1vnaBr
48jfAvkmzQyGk6OAMk2HTXY7rOZls3Cv5jhuR95h+pO6AVCloN6wq4+Y5PnyyX7Z
A3jkP/yhA0WITVryywIDAQAB
-----END PUBLIC KEY-----';

        $certificatX = self::createMock(Certificate::class);

        $certificatX->expects(self::never())->method('getPrivateKey');
        $certificatX->expects(self::exactly(2))->method('getPublicKey')->willReturn($rsaPublicKey);
        $keyring->expects(self::once())->method('getBankCertificateX')->willReturn($certificatX);
        $keyring->expects(self::once())->method('getBankCertificateE')->willReturn($certificatX);
        $keyring->expects(self::never())->method('getUserCertificateX');
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');
        $cryptService->expects(self::exactly(2))->method('calculateHash')->willReturn('myNonce');
        $cryptService->expects(self::once())->method('cryptSignatureValue')->willReturn('myNonce');

        $headerHandler        = new HeaderHandler($cryptService);
        $authSignatureHandler = new AuthSignatureHandler($cryptService);

        $sUT = new RequestHandler(null, $headerHandler, null, null, $authSignatureHandler);

        $date     = new DateTime('2010-10-10 10:10:10');
        $endDate  = new DateTime('2011-10-10 10:10:10');
        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<ebicsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Version="H004" Revision="1">
   <header authenticate="true">
      <static>
         <HostID>myHostId</HostID>
         <Nonce>myNonce</Nonce>
         <Timestamp>2010-10-10T10:10:10Z</Timestamp>
         <PartnerID />
         <UserID />
         <Product Language="de">Ebics client PHP</Product>
         <OrderDetails>
            <OrderType>FDL</OrderType>
            <OrderAttribute>DZHNN</OrderAttribute>
            <FDLOrderParams>
               <FileFormat CountryCode="test">test</FileFormat>
            </FDLOrderParams>
            <DateRange>
                <Start>2010-10-10</Start>
                <End>2011-10-10</End>
            </DateRange>

         </OrderDetails>
         <BankPubKeyDigests>
            <Authentication Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">xQR4tHm5y6NBc3o3hEhZbCN9FJM8Tygw/EvHpzFo7kc=</Authentication>
            <Encryption Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">xQR4tHm5y6NBc3o3hEhZbCN9FJM8Tygw/EvHpzFo7kc=</Encryption>
         </BankPubKeyDigests>
         <SecurityMedium>0000</SecurityMedium>
      </static>
      <mutable>
         <TransactionPhase>Initialisation</TransactionPhase>
      </mutable>
   </header>
   <AuthSignature>
      <ds:SignedInfo>
         <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
         <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />
         <ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])">
            <ds:Transforms>
               <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />
            <ds:DigestValue>bXlOb25jZQ==</ds:DigestValue>
         </ds:Reference>
      </ds:SignedInfo>
      <ds:SignatureValue>bXlOb25jZQ==</ds:SignatureValue>
   </AuthSignature>
   <body />
</ebicsRequest>

';

        self::assertXmlStringEqualsXmlString($expected, $sUT->buildFDL($bank, $user, $keyring, $date, 'test', 'test', $date, $endDate)->getContent());
    }

    public function testOkWithEndDate(): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyring = self::createMock(KeyRing::class);

        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $rsaPublicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWidncNpkqmHnFZbicgeZfmRht
/+TzVO9RtZQ7NDHPWvWYih3LBMsBKfX9rSKeso+c+feDLge5+Tp9vKt3Ip1vnaBr
48jfAvkmzQyGk6OAMk2HTXY7rOZls3Cv5jhuR95h+pO6AVCloN6wq4+Y5PnyyX7Z
A3jkP/yhA0WITVryywIDAQAB
-----END PUBLIC KEY-----';

        $certificatX = self::createMock(Certificate::class);

        $certificatX->expects(self::never())->method('getPrivateKey');
        $certificatX->expects(self::exactly(2))->method('getPublicKey')->willReturn($rsaPublicKey);
        $keyring->expects(self::once())->method('getBankCertificateX')->willReturn($certificatX);
        $keyring->expects(self::once())->method('getBankCertificateE')->willReturn($certificatX);
        $keyring->expects(self::never())->method('getUserCertificateX');
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');
        $cryptService->expects(self::exactly(2))->method('calculateHash')->willReturn('myNonce');
        $cryptService->expects(self::once())->method('cryptSignatureValue')->willReturn('myNonce');

        $headerHandler        = new HeaderHandler($cryptService);
        $authSignatureHandler = new AuthSignatureHandler($cryptService);

        $sUT = new RequestHandler(null, $headerHandler, null, null, $authSignatureHandler);

        $date     = new DateTime('2010-10-10 10:10:10');
        $endDate  = new DateTime('2011-10-10 10:10:10');
        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<ebicsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Version="H004" Revision="1">
   <header authenticate="true">
      <static>
         <HostID>myHostId</HostID>
         <Nonce>myNonce</Nonce>
         <Timestamp>2010-10-10T10:10:10Z</Timestamp>
         <PartnerID />
         <UserID />
         <Product Language="de">Ebics client PHP</Product>
         <OrderDetails>
            <OrderType>FDL</OrderType>
            <OrderAttribute>DZHNN</OrderAttribute>
            <FDLOrderParams>
               <FileFormat CountryCode="test">test</FileFormat>
            </FDLOrderParams>

         </OrderDetails>
         <BankPubKeyDigests>
            <Authentication Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">xQR4tHm5y6NBc3o3hEhZbCN9FJM8Tygw/EvHpzFo7kc=</Authentication>
            <Encryption Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">xQR4tHm5y6NBc3o3hEhZbCN9FJM8Tygw/EvHpzFo7kc=</Encryption>
         </BankPubKeyDigests>
         <SecurityMedium>0000</SecurityMedium>
      </static>
      <mutable>
         <TransactionPhase>Initialisation</TransactionPhase>
      </mutable>
   </header>
   <AuthSignature>
      <ds:SignedInfo>
         <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
         <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />
         <ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])">
            <ds:Transforms>
               <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />
            <ds:DigestValue>bXlOb25jZQ==</ds:DigestValue>
         </ds:Reference>
      </ds:SignedInfo>
      <ds:SignatureValue>bXlOb25jZQ==</ds:SignatureValue>
   </AuthSignature>
   <body />
</ebicsRequest>

';

        self::assertXmlStringEqualsXmlString($expected, $sUT->buildFDL($bank, $user, $keyring, $date, 'test', 'test', null, $endDate)->getContent());
    }

    public function testOkWithStartDate(): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyring = self::createMock(KeyRing::class);

        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $rsaPublicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWidncNpkqmHnFZbicgeZfmRht
/+TzVO9RtZQ7NDHPWvWYih3LBMsBKfX9rSKeso+c+feDLge5+Tp9vKt3Ip1vnaBr
48jfAvkmzQyGk6OAMk2HTXY7rOZls3Cv5jhuR95h+pO6AVCloN6wq4+Y5PnyyX7Z
A3jkP/yhA0WITVryywIDAQAB
-----END PUBLIC KEY-----';

        $certificatX = self::createMock(Certificate::class);

        $certificatX->expects(self::never())->method('getPrivateKey');
        $certificatX->expects(self::exactly(2))->method('getPublicKey')->willReturn($rsaPublicKey);
        $keyring->expects(self::once())->method('getBankCertificateX')->willReturn($certificatX);
        $keyring->expects(self::once())->method('getBankCertificateE')->willReturn($certificatX);
        $keyring->expects(self::never())->method('getUserCertificateX');
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');
        $cryptService->expects(self::exactly(2))->method('calculateHash')->willReturn('myNonce');
        $cryptService->expects(self::once())->method('cryptSignatureValue')->willReturn('myNonce');

        $headerHandler        = new HeaderHandler($cryptService);
        $authSignatureHandler = new AuthSignatureHandler($cryptService);

        $sUT = new RequestHandler(null, $headerHandler, null, null, $authSignatureHandler);

        $date      = new DateTime('2010-10-10 10:10:10');
        $startDate = new DateTime('2011-10-10 10:10:10');
        $expected  = '<?xml version="1.0" encoding="UTF-8"?>
<ebicsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Version="H004" Revision="1">
   <header authenticate="true">
      <static>
         <HostID>myHostId</HostID>
         <Nonce>myNonce</Nonce>
         <Timestamp>2010-10-10T10:10:10Z</Timestamp>
         <PartnerID />
         <UserID />
         <Product Language="de">Ebics client PHP</Product>
         <OrderDetails>
            <OrderType>FDL</OrderType>
            <OrderAttribute>DZHNN</OrderAttribute>
            <FDLOrderParams>
               <FileFormat CountryCode="test">test</FileFormat>
            </FDLOrderParams>

         </OrderDetails>
         <BankPubKeyDigests>
            <Authentication Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">xQR4tHm5y6NBc3o3hEhZbCN9FJM8Tygw/EvHpzFo7kc=</Authentication>
            <Encryption Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">xQR4tHm5y6NBc3o3hEhZbCN9FJM8Tygw/EvHpzFo7kc=</Encryption>
         </BankPubKeyDigests>
         <SecurityMedium>0000</SecurityMedium>
      </static>
      <mutable>
         <TransactionPhase>Initialisation</TransactionPhase>
      </mutable>
   </header>
   <AuthSignature>
      <ds:SignedInfo>
         <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
         <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />
         <ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])">
            <ds:Transforms>
               <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />
            <ds:DigestValue>bXlOb25jZQ==</ds:DigestValue>
         </ds:Reference>
      </ds:SignedInfo>
      <ds:SignatureValue>bXlOb25jZQ==</ds:SignatureValue>
   </AuthSignature>
   <body />
</ebicsRequest>

';

        self::assertXmlStringEqualsXmlString($expected, $sUT->buildFDL($bank, $user, $keyring, $date, 'test', 'test', $startDate)->getContent());
    }
}
