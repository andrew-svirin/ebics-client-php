<?php

namespace AndrewSvirin\Ebics;

use phpseclib\Crypt\RSA;

/**
 * EBICS bank representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Bank
{

   /**
    * An EbicsKeyRing instance.
    * @var KeyRing
    */
   private $keyring;

   /**
    * The HostID of the bank.
    * @var string
    */
   private $hostId;

   /**
    * The URL of the EBICS server.
    * @var string
    */
   private $url;

   /**
    * Constructor.
    *
    * @param KeyRing $keyring
    * @param string $hostId
    * @param string $url
    */
   public function __construct(KeyRing $keyring, $hostId, $url)
   {
      $this->keyring = $keyring;
      $this->hostId = (string)$hostId;
      $this->url = (string)$url;
   }

   /**
    * Getter for {keyring}.
    * @return KeyRing
    */
   public function getKeyring()
   {
      return $this->keyring;
   }

   /**
    * Getter for {authenticationKey}.
    * @return string
    */
   public function getAuthenticationKey()
   {
      $keys = $this->keyring->getKeyRingRealPath();
      return KeyRing::formatKey($keys["@{$this->hostId}"]['X']['key'], 'PUBLIC');
   }

   /**
    * Authentication {publicDigest}.
    * @return string
    */
   public function getBankAuthenticationPublicDigest()
   {
      $publicKey = $this->getAuthenticationKey();
      $rsa = new RSA();
      $rsa->setPublicKey($publicKey);

      return KeyRing::calculatePublicDigest($rsa->exponent->toHex(), $rsa->modulus->toHex());
   }

   /**
    * Getter for bankAuthenticationKeyVersion}.
    * @return string
    */
   public function getBankAuthenticationKeyVersion()
   {
      $keys = $this->keyring->getKeyRingRealPath();
      return $keys["@{$this->hostId}"]['X']['version'];
   }

   /**
    * Encryption {publicDigest}.
    * @return string
    */
   public function getBankEncryptionPublicDigest()
   {
      $publicKey = $this->getEncryptionKey();
      $rsa = new RSA();
      $rsa->setPublicKey($publicKey);

      return KeyRing::calculatePublicDigest($rsa->exponent->toHex(), $rsa->modulus->toHex());
   }

   /**
    * Getter for {encryptionKey}.
    * @return string
    */
   public function getEncryptionKey()
   {
      $keys = $this->keyring->getKeyRingRealPath();
      return KeyRing::formatKey($keys["@{$this->hostId}"]['E']['key'], 'PUBLIC');
   }

   /**
    * Getter for {bankEncryptionKeyVersion}.
    * @return string
    */
   public function getBankEncryptionKeyVersion()
   {
      $keys = $this->keyring->getKeyRingRealPath();

      return $keys["@{$this->hostId}"]['E']['version'];
   }

   /**
    * Getter for {hostId}.
    * @return string
    */
   public function getHostId()
   {
      return $this->hostId;
   }

   /**
    * Getter for {url}.
    * @return string
    */
   public function getUrl()
   {
      return $this->url;
   }

}
