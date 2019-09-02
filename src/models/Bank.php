<?php

namespace AndrewSvirin\Ebics\models;

/**
 * EBICS bank representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Bank
{

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
    * @param string $hostId
    * @param string $url
    */
   public function __construct($hostId, $url)
   {
      $this->hostId = (string)$hostId;
      $this->url = (string)$url;
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
