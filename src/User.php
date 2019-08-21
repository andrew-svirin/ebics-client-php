<?php

namespace AndrewSvirin\Ebics;

/**
 * EBICS user representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class User
{

   /**
    * An EbicsKeyRing instance.
    * @var KeyRing
    */
   private $keyring;

   /**
    * The assigned PartnerID (Kunden-ID).
    * @var string
    */
   private $partnerId;

   /**
    * The assigned UserID (Teilnehmer-ID).
    * @var string
    */
   private $userId;

   /**
    * Constructor.
    *
    * @param KeyRing $keyring
    * @param string $partnerId
    * @param string $userId
    */
   public function __construct(KeyRing $keyring, $partnerId, $userId)
   {
      $this->keyring = $keyring;
      $this->partnerId = (string)$partnerId;
      $this->userId = (string)$userId;
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
    * Getter for {partnerId}.
    * @return string
    */
   public function getPartnerId()
   {
      return $this->partnerId;
   }

   /**
    * Getter for {userId}.
    * @return string
    */
   public function getUserId()
   {
      return $this->userId;
   }

   /**
    * Getter for {encriptionKey}.
    * @return string base64
    */
   public function getEncriptionKey()
   {
      $keys = $this->keyring->getKeyRingRealPath();
      return KeyRing::formatKey($keys['#USER']['E']['pk'], 'PRIVATE', $keys['#USER']['E']['pk_iv']);
   }

   /**
    * Getter for {authorizationKey}.
    * @return string base64
    */
   public function getAuthorizationKey()
   {
      $keys = $this->keyring->getKeyRingRealPath();
      return KeyRing::formatKey($keys['#USER']['X']['pk'], 'PRIVATE', $keys['#USER']['X']['pk_iv']);
   }

}
