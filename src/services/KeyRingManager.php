<?php

namespace AndrewSvirin\Ebics\services;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\factories\KeyRingFactory;
use AndrewSvirin\Ebics\models\KeyRing;

/**
 * EBICS key ring representation manage one key ring.
 *
 * An EbicsKeyRing instance can hold sets of private user keys and/or public
 * bank keys. Private user keys are always stored AES encrypted by the
 * specified passphrase (derivated by PBKDF2). For each key file on disk or
 * same key dictionary a singleton instance is created.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class KeyRingManager
{

   /**
    * The path to a key file.
    * @var string
    */
   private $keyRingRealPath;

   /**
    * The passphrase by which all private keys are encrypted/decrypted.
    * @var string
    */
   private $passphrase;


   /**
    * Constructor.
    * @param string $keyRingRealPath
    * @param string $passphrase
    */
   public function __construct($keyRingRealPath, $passphrase)
   {
      $this->keyRingRealPath = $keyRingRealPath;
      $this->passphrase = $passphrase;
   }

   /**
    * Load Keyring from the saved file or create new one.
    * @return KeyRing
    * @throws EbicsException
    */
   public function loadKeyRing(): KeyRing
   {
      if (is_file($this->keyRingRealPath) && ($content = file_get_contents($this->keyRingRealPath)) && !empty($content))
      {
         if (!($data = json_decode($content, true)))
         {
            throw new EbicsException('Can not extract keys from file.');
         }
         $result = KeyRingFactory::buildKeyRingFromData($data);
      }
      else
      {
         $result = new KeyRing();
      }
      return $result;
   }

   /**
    * Save KeyRing to file.
    * @param KeyRing $keyRing
    * @throws EbicsException
    */
   public function saveKeyRing(KeyRing $keyRing)
   {
      $data = KeyRingFactory::buildDataFromKeyRing($keyRing);
      if (!file_put_contents($this->keyRingRealPath, json_encode($data, JSON_PRETTY_PRINT)))
      {
         throw new EbicsException('Can not save keys to file.');
      }
   }

}
