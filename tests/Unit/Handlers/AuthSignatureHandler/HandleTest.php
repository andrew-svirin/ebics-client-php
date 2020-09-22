<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\AuthSignatureHandler;

use AndrewSvirin\Ebics\Handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass AuthSignatureHandler
 */
class HandleTest extends TestCase
{
    public function testOk(): void
    {
        $cryptService = self::createMock(CryptService::class);

        $sUT     = new AuthSignatureHandler($cryptService);
        $keyring = new KeyRing();

        $domDocument = new Request();
        $domElement  = $domDocument->createElement('test');
        $domDocument->appendChild($domElement);

        $domDocument = $sUT->handle($keyring, $domDocument, $domElement);

        self::assertXmlStringEqualsXmlString(<<<'XML'
<?xml version="1.0"?>
<test>
    <AuthSignature>
        <ds:SignedInfo
            xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
            <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
            <ds:Reference URI="#xpointer(//*[@authenticate='true'])">
                <ds:Transforms>
                    <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
                </ds:Transforms>
                <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                <ds:DigestValue/>
            </ds:Reference>
        </ds:SignedInfo>
        <ds:SignatureValue/>
    </AuthSignature>
</test>
XML, (string) $domDocument->saveXML());
    }
}
