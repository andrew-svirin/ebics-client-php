<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\RequestHandler;

use AndrewSvirin\Ebics\Handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\Handlers\HeaderHandler;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Models\Version;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass RequestHandler
 */
class BuildHTDTest extends TestCase
{
    public function testOk(): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyring = self::createMock(KeyRing::class);

        $certificatX = self::createMock(Certificate::class);

        $certificatX->expects(self::never())->method('getPrivateKey');
        $certificatX->expects(self::never())->method('getPublicKey');
        $keyring->expects(self::once())->method('getBankCertificateX')->willReturn($certificatX);
        $keyring->expects(self::once())->method('getBankCertificateE')->willReturn($certificatX);
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');
        $bank->expects(self::any())->method('getVersion')->willReturn(Version::V25);

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');
        $cryptService->expects(self::exactly(2))->method('calculateHash')->willReturn('myNonce');
        $cryptService->expects(self::once())->method('cryptSignatureValue')->willReturn('myNonce');
        $cryptService->expects(self::exactly(2))->method('calculateDigest')->willReturn('myDigest');

        $headerHandler        = new HeaderHandler($cryptService);
        $authSignatureHandler = new AuthSignatureHandler($cryptService);

        $sUT = new RequestHandler(null, $headerHandler, null, null, $authSignatureHandler);

        $date     = new DateTime();
        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<ebicsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Version="H004" Revision="1">
   <header authenticate="true">
      <static>
         <HostID>myHostId</HostID>
         <Nonce>myNonce</Nonce>
         <Timestamp>' . $date->format('Y-m-d\TH:i:s\Z') . '</Timestamp>
         <PartnerID />
         <UserID />
         <Product Language="de">Ebics client PHP</Product>
         <OrderDetails>
            <OrderType>HTD</OrderType>
            <OrderAttribute>DZHNN</OrderAttribute>
            <StandardOrderParams />
         </OrderDetails>
         <BankPubKeyDigests>
            <Authentication Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">bXlEaWdlc3Q=</Authentication>
            <Encryption Version="" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">bXlEaWdlc3Q=</Encryption>
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

        self::assertXmlStringEqualsXmlString($expected, $sUT->buildHTD($bank, $user, $keyring, $date)->getContent());
    }
}
