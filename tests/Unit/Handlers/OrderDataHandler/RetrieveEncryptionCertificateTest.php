<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\OrderDataHandler;

use AndrewSvirin\Ebics\Factories\CertificateFactory;
use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\OrderData;
use PHPUnit\Framework\TestCase;

use function base64_encode;

/**
 * @coversDefaultClass OrderDataHandler
 */
class RetrieveEncryptionCertificateTest extends TestCase
{
    public function testNotCertified(): void
    {
        $certificat = new Certificate('test', 'test');

        $certificateFactory = self::createMock(CertificateFactory::class);
        $certificateFactory->expects(self::once())->method('buildCertificateEFromDetails')->with('mod', 'expo', null)->willReturn($certificat);

        $bank = new Bank('test', 'test', false);

        $sUT = new OrderDataHandler($certificateFactory);

        $orderData = new OrderData('<?xml version="1.0"?>
        <EncryptionPubKeyInfo xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <PubKeyValue xmlns="urn:org:ebics:H004">
                <ds:RSAKeyValue>
                    <ds:Modulus>' . base64_encode('mod') . '</ds:Modulus>
                    <ds:Exponent>' . base64_encode('expo') . '</ds:Exponent>
                </ds:RSAKeyValue>
            </PubKeyValue>
        </EncryptionPubKeyInfo>
        ');

        self::assertSame($certificat, $sUT->retrieveEncryptionCertificate($orderData, $bank));
    }

    public function testCertified(): void
    {
        $certificat = new Certificate('test', 'test');

        $certificateFactory = self::createMock(CertificateFactory::class);
        $certificateFactory->expects(self::once())->method('buildCertificateEFromDetails')->with('mod', 'expo', 'cert')->willReturn($certificat);

        $bank = new Bank('test', 'test', false);

        $sUT = new OrderDataHandler($certificateFactory);

        $orderData = new OrderData('<?xml version="1.0"?>
        <EncryptionPubKeyInfo xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <ds:X509Data>
                 <ds:X509Certificate>' . base64_encode('cert') . '</ds:X509Certificate>
            </ds:X509Data>
            <PubKeyValue xmlns="urn:org:ebics:H004">
                <ds:RSAKeyValue>
                    <ds:Modulus>' . base64_encode('mod') . '</ds:Modulus>
                    <ds:Exponent>' . base64_encode('expo') . '</ds:Exponent>
                </ds:RSAKeyValue>
            </PubKeyValue>
        </EncryptionPubKeyInfo>
        ');

        self::assertSame($certificat, $sUT->retrieveEncryptionCertificate($orderData, $bank));
    }
}
