<?php

namespace AndrewSvirin\Ebics\Tests\Handlers;

use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\OrderDataHandlerV25;
use AndrewSvirin\Ebics\Handlers\Traits\H004Trait;
use AndrewSvirin\Ebics\Handlers\Traits\H00XTrait;
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
    use H00XTrait;
    use H004Trait;

    /**
     * @var OrderDataHandler
     */
    private $orderDataHandler;

    public function setUp(): void
    {
        parent::setUp();
        $client = $this->setupClientV25(3);
        $this->setupKeys($client->getKeyring());
        $this->orderDataHandler = new OrderDataHandlerV25($client->getUser(), $client->getKeyring());
    }

    /**
     * @group HandleINI
     */
    public function testHandleINI()
    {
        $h00x = $this->getH00XVersion();
        $ini = file_get_contents($this->fixtures.'/ini.xml');
        $iniXML = new Request();
        $iniXML->loadXML($ini);
        $iniXPath = $this->prepareH00XXPath($iniXML);
        $orderData = $iniXPath->query("//$h00x:body/$h00x:DataTransfer/$h00x:OrderData")->item(0)->nodeValue;
        $orderDataDeUn = gzuncompress(base64_decode($orderData));
        $orderDataXML = new CustomerINI();
        $orderDataXML->loadXML($orderDataDeUn);
        $orderDataXPath = $this->prepareS001XPath($orderDataXML);
        $iniDatetime = $orderDataXPath->query("//S001:SignaturePubKeyInfo/S001:PubKeyValue/S001:TimeStamp")->item(0)->nodeValue;
        self::assertNotEmpty($iniDatetime);
    }
}
