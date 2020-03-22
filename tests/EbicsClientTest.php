<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class EbicsTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class EbicsClientTest extends AbstractEbicsTestCase
{

   /**
    * @dataProvider credentialsDataProvider
    * @throws EbicsException
    */
   public function setUp()
   {
      parent::setUp();
      $this->setupClient();
   }

   /**
    * @group HEV
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testHEV()
   {
      $hev = $this->client->HEV();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH000ReturnCode($hev);
      $reportText = $responseHandler->retrieveH000ReportText($hev);
      $this->assertResponseCorrect($code, $reportText);
   }

   /**
    * @group INI
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws EbicsException
    */
   public function testINI()
   {
      $ini = $this->client->INI();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($ini);
      $reportText = $responseHandler->retrieveH004ReportText($ini);
      $this->assertResponseCorrect($code, $reportText);
      $this->keyRingManager->saveKeyRing($this->keyRing);
   }

   /**
    * @depends testINI
    * @group HIA
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws EbicsException
    */
   public function testHIA()
   {
      $hia = $this->client->HIA();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($hia);
      $reportText = $responseHandler->retrieveH004ReportText($hia);
      $this->assertResponseCorrect($code, $reportText);
      $this->keyRingManager->saveKeyRing($this->keyRing);
   }

   /**
    * Run first HIA and Activate account in bank panel.
    * @depends testHIA
    * @group HPB
    * @throws ClientExceptionInterface
    * @throws EbicsException
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testHPB()
   {
      $hpb = $this->client->HPB();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($hpb);
      $reportText = $responseHandler->retrieveH004ReportText($hpb);
      $this->assertResponseCorrect($code, $reportText);
      $this->keyRingManager->saveKeyRing($this->keyRing);
   }

   /**
    * @depends testHPB
    * @group HPD
    * @throws ClientExceptionInterface
    * @throws EbicsException
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testHPD()
   {
      $hpd = $this->client->HPD();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($hpd);
      $reportText = $responseHandler->retrieveH004ReportText($hpd);
      $this->assertResponseCorrect($code, $reportText);
   }

   /**
    * @depends testHPB
    * @group HAA
    * @throws ClientExceptionInterface
    * @throws EbicsException
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testHAA()
   {
      $haa = $this->client->HAA();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($haa);
      $reportText = $responseHandler->retrieveH004ReportText($haa);
      $this->assertResponseCorrect($code, $reportText);
   }

   /**
    * @depends testHPB
    * @group VMK
    * @throws ClientExceptionInterface
    * @throws EbicsException
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testVMK()
   {
      $vmk = $this->client->VMK();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($vmk);
      $reportText = $responseHandler->retrieveH004ReportText($vmk);
      $this->assertResponseCorrect($code, $reportText);
   }

   /**
    * @depends testHPB
    * @group STA
    * @throws ClientExceptionInterface
    * @throws EbicsException
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testSTA()
   {
      $sta = $this->client->STA();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($sta);
      $reportText = $responseHandler->retrieveH004ReportText($sta);
      $this->assertResponseCorrect($code, $reportText);
   }

}