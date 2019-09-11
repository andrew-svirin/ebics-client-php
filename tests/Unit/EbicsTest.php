<?php

namespace AndrewSvirin\tests\Unit;

use AndrewSvirin\Ebics\models\Bank;
use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\handlers\ResponseHandler;
use AndrewSvirin\Ebics\models\KeyRing;
use AndrewSvirin\Ebics\services\KeyRingManager;
use AndrewSvirin\Ebics\models\User;
use DateTime;
use PHPUnit\Framework\TestCase;
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
final class EbicsTest extends TestCase
{

   var $data = __DIR__ . '/../_data';
   var $fixtures = __DIR__ . '/../_fixtures';

   /**
    * @var EbicsClient
    */
   private $client;

   /**
    * @var KeyRingManager
    */
   private $keyRingManager;

   /**
    * @var KeyRing
    */
   private $keyRing;

   /**
    * @throws EbicsException
    */
   public function setUp()
   {
      parent::setUp();
      $credentials = json_decode(file_get_contents($this->data . '/credentials.json'));
      $keyRingRealPath = $this->data . '/workspace/keyring.json';
      $this->keyRingManager = new KeyRingManager($keyRingRealPath, 'test123');
      $this->keyRing = $this->keyRingManager->loadKeyRing();
      $bank = new Bank($credentials->hostId, $credentials->hostURL);
      $user = new User($credentials->partnerId, $credentials->userId);
      $this->client = new EbicsClient($bank, $user, $this->keyRing);
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
      if ($this->keyRing->getUserCertificateA())
      {
         return;
      }
      $ini = $this->client->INI();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($ini);
      $reportText = $responseHandler->retrieveH004ReportText($ini);
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
      $this->keyRingManager->saveKeyRing($this->keyRing);
   }

   /**
    * Run first INI.
    * @group HIA
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws EbicsException
    */
   public function testHIA()
   {
      if ($this->keyRing->getUserCertificateX() || $this->keyRing->getUserCertificateE())
      {
         return;
      }
      $hia = $this->client->HIA();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($hia);
      $reportText = $responseHandler->retrieveH004ReportText($hia);
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
      $this->keyRingManager->saveKeyRing($this->keyRing);
   }

   /**
    * Run first HIA.
    * @group HPB
    * @throws ClientExceptionInterface
    * @throws EbicsException
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testHPB()
   {
      if ($this->keyRing->getBankCertificateX() || $this->keyRing->getBankCertificateE())
      {
         return;
      }
      $hpb = $this->client->HPB();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($hpb);
      $reportText = $responseHandler->retrieveH004ReportText($hpb);
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
      $this->keyRingManager->saveKeyRing($this->keyRing);
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
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
   }

   /**
    * Run first HPB.
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
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
   }

   /**
    * Run first HPB.
    * @group VMK
    * @throws ClientExceptionInterface
    * @throws EbicsException
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testVMK()
   {
      $vmk = $this->client->VMK(null,
         DateTime::createFromFormat('Y-m-d', '2005-01-01'),
         DateTime::createFromFormat('Y-m-d', '2019-09-01')
      );
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($vmk);
      $reportText = $responseHandler->retrieveH004ReportText($vmk);
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
   }

   /**
    * Run first HPB.
    * @group STA
    * @throws ClientExceptionInterface
    * @throws EbicsException
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function testSTA()
   {
      $vmk = $this->client->STA(null,
         DateTime::createFromFormat('Y-m-d', '2005-01-01'),
         DateTime::createFromFormat('Y-m-d', '2019-09-01')
      );
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveH004ReturnCode($vmk);
      $reportText = $responseHandler->retrieveH004ReportText($vmk);
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
   }

   /**
    * Run first HPB.
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
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
   }

}