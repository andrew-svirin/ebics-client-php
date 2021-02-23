<?php

namespace AndrewSvirin\Ebics\Tests\Handlers;

use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\CustomerINI;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;

/**
 * Class RequestFactoryTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group order-data-handler
 */
class OrderDataHandlerTest extends AbstractEbicsTestCase
{
    use XPathTrait;

    /**
     * @var OrderDataHandler
     */
    private $orderDataHandler;

    public function setUp(): void
    {
        parent::setUp();
        $client = $this->setupClient(3);
        $this->setupKeys($client->getKeyRing());
        $this->orderDataHandler = new OrderDataHandler($client->getBank(), $client->getUser(), $client->getKeyRing());
    }

    /**
     * @group HandleINI
     */
    public function testHandleINI()
    {
        $ini = file_get_contents($this->fixtures . '/ini.xml');
        $iniXML = new Request();
        $iniXML->loadXML($ini);
        $iniXPath = $this->prepareH004XPath($iniXML);
        $orderData = $iniXPath->query('//H004:body/H004:DataTransfer/H004:OrderData')->item(0)->nodeValue;
        $orderDataDeUn = gzuncompress(base64_decode($orderData));
        $orderDataXML = new CustomerINI();
        $orderDataXML->loadXML($orderDataDeUn);
        $orderDataXPath = $this->prepareS001XPath($orderDataXML);
        $iniDatetime = $orderDataXPath->query('//S001:SignaturePubKeyInfo/S001:PubKeyValue/S001:TimeStamp')->item(0)->nodeValue;
        $this->assertNotEmpty($iniDatetime);

        // TODO: Extract public key.
//      $ini2XML = clone $iniXML;
//      $this->orderDataHandler->handleINI(
//         $ini2XML,
//         $this->keyRing->getUserCertificateA(),
//         DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $iniDatetime)
//      );
    }
}
