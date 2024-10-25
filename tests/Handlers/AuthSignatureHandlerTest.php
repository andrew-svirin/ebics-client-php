<?php

namespace AndrewSvirin\Ebics\Tests\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandlerV25;
use AndrewSvirin\Ebics\Handlers\Traits\H004Trait;
use AndrewSvirin\Ebics\Handlers\Traits\H00XTrait;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;

/**
 * Class EbicsTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group auth-signature-handler
 */
class AuthSignatureHandlerTest extends AbstractEbicsTestCase
{
    use H00XTrait;
    use H004Trait;

    /**
     * @var AuthSignatureHandler
     */
    private $authSignatureHandler;

    public function setUp(): void
    {
        parent::setUp();
        $credentialsId = 1;
        $client = $this->setupClientV25($credentialsId);
        $this->setupKeys($client->getKeyring());
        $this->authSignatureHandler = new AuthSignatureHandlerV25($client->getKeyring());
    }

    /**
     * Generate auth signature for working example.
     *
     * @group DigestValue
     *
     * @throws EbicsException
     */
    public function testDigestValue()
    {
        $h00x = $this->getH00XVersion();
        $hpb = file_get_contents($this->fixtures.'/hpb.xml');
        $hpbXML = new Request();
        $hpbXML->loadXML($hpb);
        $hpbXPath = $this->prepareH00XXPath($hpbXML);

        $hpb2XML = clone $hpbXML;
        $hpb2XPath = $this->prepareH00XXPath($hpb2XML);
        $hpb2Header = $hpb2XPath->query("//$h00x:header")->item(0);
        $authSignature2 = $hpb2XPath->query("//$h00x:AuthSignature")->item(0);
        $authSignature2->parentNode->removeChild($authSignature2);

        $this->authSignatureHandler->handle($hpb2XML, $hpb2Header);

        // Rewind. Because after remove and insert XML tree do not work correctly.
        $hpb2XML->loadXML($hpb2XML->saveXML());
        $hpb2XPath = $this->prepareH00XXPath($hpb2XML);

        $digestValue = $hpbXPath->query("//$h00x:AuthSignature/ds:SignedInfo/ds:Reference/ds:DigestValue")->item(0)->nodeValue;
        $digestValue2 = $hpb2XPath->query("//$h00x:AuthSignature/ds:SignedInfo/ds:Reference/ds:DigestValue")->item(0)->nodeValue;
        self::assertEquals($digestValue, $digestValue2);
    }

    /**
     * Generate auth signature for working example.
     *
     * @group SignatureValue
     *
     * @throws EbicsException
     */
    public function testSignatureValue()
    {
        $h00x = $this->getH00XVersion();
        $hpb = file_get_contents($this->fixtures.'/hpb.xml');
        $request = new Request();
        $request->loadXML($hpb);
        $requestXpath = $this->prepareH00XXPath($request);
        $digestValue = $requestXpath->query("//$h00x:AuthSignature/ds:SignatureValue")->item(0)->nodeValue;

        $request2 = clone $request;
        $request2XPath = $this->prepareH00XXPath($request2);

        // Remove AuthSignature.
        $authSignature2 = $request2XPath->query("//$h00x:AuthSignature")->item(0);
        $authSignature2->parentNode->removeChild($authSignature2);
        // Remove Body for shift after AuthSignature.
        $body2 = $request2XPath->query("//$h00x:body")->item(0);
        $body2->parentNode->removeChild($body2);

        $request2Request = $request2XPath->query("/$h00x:ebicsNoPubKeyDigestsRequest")->item(0);
        $request2RequestHeader = $request2XPath->query("//$h00x:header")->item(0);
        $this->authSignatureHandler->handle($request2, $request2RequestHeader);
        // Add removed body after AuthSignature.
        $request2Request->appendChild($body2);

        // Rewind. Because after remove and insert XML tree do not work correctly.
        $request2->loadXML($request2->saveXML());
        $request2XPath = $this->prepareH00XXPath($request2);

        $digestValue2 = $request2XPath->query("//$h00x:AuthSignature/ds:SignatureValue")->item(0)->nodeValue;

        self::assertEquals($digestValue, $digestValue2);

//      $hostResponse = $this->client->post($request2);
//      $hostResponseContent = $hostResponse->getContent();
//      return;
    }
}
