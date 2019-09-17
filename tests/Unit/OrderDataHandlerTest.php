<?php

use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\OrderData as OrderDataAlias;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\tests\common\EbicsTestCase;

/**
 * Class RequestFactoryTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderDataHandlerTest extends EbicsTestCase
{

   use XPathTrait;

   /**
    * @var OrderDataHandler
    */
   private $orderDataHandler;

   /**
    * @throws EbicsException
    */
   public function setUp()
   {
      parent::setUp();
      $this->setupClient();
      $this->setupKeys();
      $this->orderDataHandler = new OrderDataHandler($this->bank, $this->user, $this->keyRing);
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
      $orderDataXML = new OrderDataAlias();
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