<?php

namespace AndrewSvirin\tests\Unit;

use AndrewSvirin\Ebics\models\Bank;
use AndrewSvirin\Ebics\EBICSClient;
use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\handlers\ResponseHandler;
use AndrewSvirin\Ebics\services\KeyRingManager;
use AndrewSvirin\Ebics\models\User;
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
    * @var EBICSClient
    */
   private $client;

   /**
    * @var KeyRingManager
    */
   private $keyRingManager;

   /**
    * @throws EbicsException
    */
   public function setUp()
   {
      parent::setUp();
      $credentials = json_decode(file_get_contents($this->data . '/credentials.json'));
      $keyRingRealPath = $this->data . '/workspace/keyring.json';
      $this->keyRingManager = new KeyRingManager($keyRingRealPath, 'test123');
      $keyRing = $this->keyRingManager->loadKeyRing();
      $bank = new Bank($credentials->hostId, $credentials->hostURL);
      $user = new User($credentials->partnerId, $credentials->userId);
      $this->client = new EBICSClient($bank, $user, $keyRing);
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
      if ($this->client->getKeyRing()->getUserCertificateA())
      {
         return;
      }
      $ini = $this->client->INI();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveKeyManagementResponseReturnCode($ini);
      $reportText = $responseHandler->retrieveKeyManagementResponseReportText($ini);
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
      $this->keyRingManager->saveKeyRing($this->client->getKeyRing());
   }

   /**
    * @group HIA
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws EbicsException
    */
   public function testHIA()
   {
      if ($this->client->getKeyRing()->getUserCertificateX() || $this->client->getKeyRing()->getUserCertificateE())
      {
         return;
      }
      $hia = $this->client->HIA();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveKeyManagementResponseReturnCode($hia);
      $reportText = $responseHandler->retrieveKeyManagementResponseReportText($hia);
      $this->assertEquals($code, '000000');
      $this->assertEquals($reportText, '[EBICS_OK] OK');
      $this->keyRingManager->saveKeyRing($this->client->getKeyRing());
   }

   /**
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
   }

}