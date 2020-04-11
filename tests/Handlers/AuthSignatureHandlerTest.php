<?php

namespace AndrewSvirin\Ebics\Tests\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;

/**
 * Class EbicsTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class AuthSignatureHandlerTest extends AbstractEbicsTestCase
{
    use XPathTrait;

    /**
     * @var AuthSignatureHandler
     */
    private $authSignatureHandler;

    /**
     * @throws EbicsException
     */
    public function setUp()
    {
        parent::setUp();
        $this->setupClient();
        $this->setupKeys();
        $this->authSignatureHandler = new AuthSignatureHandler($this->keyRing);
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
        $hpb = file_get_contents($this->fixtures . '/hpb.xml');
        $hpbXML = new Request();
        $hpbXML->loadXML($hpb);
        $hpbXPath = $this->prepareH004XPath($hpbXML);

        $hpb2XML = clone $hpbXML;
        $hpb2XPath = $this->prepareH004XPath($hpb2XML);
        $hpb2Request = $hpb2XPath->query('/H004:ebicsNoPubKeyDigestsRequest')->item(0);
        $authSignature2 = $hpb2XPath->query('//H004:AuthSignature')->item(0);
        $authSignature2->parentNode->removeChild($authSignature2);

        $this->authSignatureHandler->handle($hpb2XML, $hpb2Request);

        // Rewind. Because after remove and insert XML tree do not work correctly.
        $hpb2XML->loadXML($hpb2XML->saveXML());
        $hpb2XPath = $this->prepareH004XPath($hpb2XML);

        $digestValue = $hpbXPath->query('//H004:AuthSignature/ds:SignedInfo/ds:Reference/ds:DigestValue')->item(0)->nodeValue;
        $digestValue2 = $hpb2XPath->query('//H004:AuthSignature/ds:SignedInfo/ds:Reference/ds:DigestValue')->item(0)->nodeValue;
        $this->assertEquals($digestValue, $digestValue2);
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
        $hpb = file_get_contents($this->fixtures . '/hpb.xml');
        $request = new Request();
        $request->loadXML($hpb);
        $requestXpath = $this->prepareH004XPath($request);
        $digestValue = $requestXpath->query('//H004:AuthSignature/ds:SignatureValue')->item(0)->nodeValue;

        $request2 = clone $request;
        $request2XPath = $this->prepareH004XPath($request2);

        // Remove AuthSignature.
        $authSignature2 = $request2XPath->query('//H004:AuthSignature')->item(0);
        $authSignature2->parentNode->removeChild($authSignature2);
        // Remove Body for shift after AuthSignature.
        $body2 = $request2XPath->query('//H004:body')->item(0);
        $body2->parentNode->removeChild($body2);

        $request2Request = $request2XPath->query('/H004:ebicsNoPubKeyDigestsRequest')->item(0);
        $this->authSignatureHandler->handle($request2, $request2Request);
        // Add removed body after AuthSignature.
        $request2Request->appendChild($body2);

        // Rewind. Because after remove and insert XML tree do not work correctly.
        $request2->loadXML($request2->saveXML());
        $request2XPath = $this->prepareH004XPath($request2);

        $digestValue2 = $request2XPath->query('//H004:AuthSignature/ds:SignatureValue')->item(0)->nodeValue;

        $this->assertEquals($digestValue, $digestValue2);

//      $hostResponse = $this->client->post($request2);
//      $hostResponseContent = $hostResponse->getContent();
//      return;
    }
}
