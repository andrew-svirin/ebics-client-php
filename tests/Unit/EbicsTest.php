<?php

namespace AndrewSvirin\tests\Unit;

use AndrewSvirin\Ebics\Bank;
use AndrewSvirin\Ebics\Client;
use AndrewSvirin\Ebics\handlers\ResponseHandler;
use AndrewSvirin\Ebics\KeyRing;
use AndrewSvirin\Ebics\User;
use PHPUnit\Framework\TestCase;

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
    * @group INI
    */
   public function testINI()
   {
      $credentials = json_decode(file_get_contents($this->data . '/credentials.json'));
      $keyRingRealPath = realpath($this->data . '/workspace/keyring.json');
      $keyring = new KeyRing($keyRingRealPath, 'test123', base64_decode($credentials->A006CertB64));
      $bank = new Bank($keyring, $credentials->hostId, $credentials->hostURL);
      $user = new User($keyring, $credentials->partnerId, $credentials->userId);
      $client = new Client($bank, $user, $keyring);
      $ini = $client->INI();
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveKeyManagementResponseReturnCode($ini);
      $this->assertEquals($code, '000000');
      $reportText = $responseHandler->retrieveKeyManagementResponseReportText($ini);
      $this->assertEquals($reportText, '[EBICS_OK] OK');
   }

   /**
    * @group HIA
    */
   public function testHIA()
   {
      $credentials = json_decode(file_get_contents($this->data . '/credentials.json'));
      $keyRingRealPath = realpath($this->data . '/workspace/keyring.json');
      $keyring = new KeyRing($keyRingRealPath, 'test123', base64_decode($credentials->A006CertB64));
      $bank = new Bank($keyring, $credentials->hostId, $credentials->hostURL);
      $user = new User($keyring, $credentials->partnerId, $credentials->userId);
      $client = new Client($bank, $user, $keyring);
      $data = file_get_contents($this->fixtures . '/hia.xml');
      $hia = $client->HIA($data);
      $responseHandler = new ResponseHandler();
      $code = $responseHandler->retrieveKeyManagementResponseReturnCode($hia);
      $this->assertEquals($code, '000000');
      $reportText = $responseHandler->retrieveKeyManagementResponseReportText($hia);
      $this->assertEquals($reportText, '[EBICS_OK] OK');
   }

}