<?php

namespace AndrewSvirin\Ebics\models;

/**
 * Class Certificate represents Certificate model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Certificate
{

   private $content;

   public function __construct(string $content)
   {
      $this->content = $content;
   }

   /**
    * @return string
    */
   public function getContent(): string
   {
      return $this->content;
   }

   /**
    * Represents Certificate in the structure X509.
    * @return CertificateX509
    */
   public function toX509(): CertificateX509
   {
      $x509 = new CertificateX509();
      $x509->loadX509($this->content);
      return $x509;
   }

}