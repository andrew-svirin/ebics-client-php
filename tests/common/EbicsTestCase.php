<?php

namespace AndrewSvirin\tests\common;

use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Services\KeyRingManager;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Utils\EnvUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestCase extends basic TestCase for add extra setups.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class EbicsTestCase extends TestCase
{

   /**
    * Algo to encode/decode session.
    */
   const ENCRYPT_ALGO = 'AES-128-ECB';

   var $data = __DIR__ . '/../_data';
   var $fixtures = __DIR__ . '/../_fixtures';

   /**
    * @var int
    */
   private $credentialsId = 3;

   /**
    * @var bool
    */
   private $debug = false;

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
      $credentialsDataProvider = $this->credentialsDataProvider();
      $credentials = $credentialsDataProvider[$this->credentialsId];
      $keyRingRealPath = sprintf('%s/workspace/keyring_%d.json', $this->data, $this->credentialsId);
      $this->bank = new Bank($credentials['hostId'], $credentials['hostURL'], $credentials['hostIsCertified']);
      $this->user = new User($credentials['partnerId'], $credentials['userId']);
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

   /**
    * Validate response data.
    * @param $code
    * @param $reportText
    * @return void
    */
   protected function assertResponseCorrect($code, $reportText)
   {
      if ($this->debug)
      {
         $this->assertEquals($code, '000000', $reportText);
         $this->assertEquals($reportText, '[EBICS_OK] OK');
      }
      else
      {
         $this->assertNotEmpty($code);
         $this->assertNotEmpty($reportText);
      }
   }

   /**
    * Client credentials data provider.
    * @return array
    */
   public function credentialsDataProvider(): array
   {
      $secret = EnvUtil::getSecret();
      $this->generateCredentialsDataProvider($secret);
      $credentialsEnc = json_decode(file_get_contents($this->credentialsFilePath()), true);
      $credentialsDec = [];
      foreach ($credentialsEnc as $i => $credentials)
      {
         $credentialsDec[$i] = [
            'hostId' => $this->decrypt($credentials['hostId'], $secret),
            'hostURL' => $this->decrypt($credentials['hostURL'], $secret),
            'hostIsCertified' => (bool)$this->decrypt($credentials['hostIsCertified'], $secret),
            'partnerId' => $this->decrypt($credentials['partnerId'], $secret),
            'userId' => $this->decrypt($credentials['userId'], $secret),
         ];
      }
      return $credentialsDec;
   }

   /**
    * @return string
    */
   private function credentialsFilePath()
   {
      return $this->data . '/credentials.json';
   }

   /**
    * Credentials data provider.
    * Extract from env debugging variables and populate data.
    * @param string $secret
    */
   private function generateCredentialsDataProvider(string $secret)
   {
      if (!($credentials1 = EnvUtil::getCredentials1()) || !($credentials2 = EnvUtil::getCredentials2()) || !($credentials3 = EnvUtil::getCredentials3()))
      {
         return;
      }
      $credentialsEnc = [];
      for ($i = 1; $i < 4; $i++)
      {

         switch ($i)
         {
            case 1:
               $credentials = $credentials1;
               break;
            case 2:
               $credentials = $credentials2;
               break;
            case 3:
               $credentials = $credentials3;
               break;
            default:
               $credentials = null;
         }
         if (null === $credentials)
         {
            continue;
         }
         $credentialsEnc[$i] = [
            'hostId' => $this->encrypt($credentials['hostId'], $secret),
            'hostURL' => $this->encrypt($credentials['hostURL'], $secret),
            'hostIsCertified' => $this->encrypt($credentials['hostIsCertified'], $secret),
            'partnerId' => $this->encrypt($credentials['partnerId'], $secret),
            'userId' => $this->encrypt($credentials['userId'], $secret),
         ];
      }
      file_put_contents($this->credentialsFilePath(), json_encode($credentialsEnc, JSON_PRETTY_PRINT));
   }

   /**
    * Encrypt text.
    * @param string $text
    * @param string $secret
    * @return string
    */
   private function encrypt(string $text, string $secret): string
   {
      return base64_encode(openssl_encrypt($text, self::ENCRYPT_ALGO, $secret));
   }

   /**
    * Decrypt text.
    * @param string $text
    * @param string $secret
    * @return string
    */
   private function decrypt(string $text, string $secret)
   {
      return openssl_decrypt(base64_decode($text), self::ENCRYPT_ALGO, $secret);
   }

}