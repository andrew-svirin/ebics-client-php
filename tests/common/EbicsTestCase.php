<?php

namespace AndrewSvirin\tests\common;

use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Services\KeyRingManager;
use AndrewSvirin\Ebics\Models\User;
use PHPUnit\Framework\TestCase;

/**
 * Class TestCase extends basic TestCase for add extra setups.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class EbicsTestCase extends TestCase
{

   var $data = __DIR__ . '/../_data';
   var $fixtures = __DIR__ . '/../_fixtures';

   /**
    * @var EbicsClient
    */
   protected $client;

   /**
    * @var KeyRingManager
    */
   protected $keyRingManager;

   /**
    * @var KeyRing
    */
   protected $keyRing;

   /**
    * @var Bank
    */
   protected $bank;

   /**
    * @var User
    */
   protected $user;

   /**
    * @throws EbicsException
    */
   protected function setupClient()
   {
      $keyRingRealPath = $this->data . '/workspace/keyring_1.json';
      $credentials = json_decode(file_get_contents($this->data . '/credentials_1.json'));
      $this->bank = new Bank($credentials->hostId, $credentials->hostURL, $credentials->hostIsCertified);
      $this->user = new User($credentials->partnerId, $credentials->userId);
      $this->keyRingManager = new KeyRingManager($keyRingRealPath, 'test123');
      $this->keyRing = $this->keyRingManager->loadKeyRing();
      $this->client = new EbicsClient($this->bank, $this->user, $this->keyRing);
   }

   protected function setupKeys()
   {
      $keys = json_decode(file_get_contents($this->fixtures . '/keys.json'));
      $this->keyRing->setPassword('mysecret');
      $this->keyRing->setUserCertificateX(new Certificate(
         $this->keyRing->getUserCertificateX()->getType(),
         $this->keyRing->getUserCertificateX()->getPublicKey(),
         $keys->X002,
         $this->keyRing->getUserCertificateX()->getContent()
      ));
      $this->keyRing->setUserCertificateE(new Certificate(
         $this->keyRing->getUserCertificateE()->getType(),
         $this->keyRing->getUserCertificateE()->getPublicKey(),
         $keys->E002,
         $this->keyRing->getUserCertificateX()->getContent()
      ));
      $this->keyRing->setUserCertificateA(new Certificate(
         $this->keyRing->getUserCertificateA()->getType(),
         $this->keyRing->getUserCertificateA()->getPublicKey(),
         $keys->A006,
         $this->keyRing->getUserCertificateX()->getContent()
      ));
   }

}