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

   const TYPE_A = 'A';
   const TYPE_X = 'X';
   const TYPE_E = 'E';

   /**
    * @var string
    */
   private $content;

   /**
    * @var string
    */
   private $type;

   /**
    * @var array
    */
   private $keys;

   public function __construct(string $content, string $type, array $keys)
   {
      $this->content = $content;
      $this->type = $type;
      $this->keys = $keys;
   }

   /**
    * @return string
    */
   public function getContent(): string
   {
      return $this->content;
   }

   /**
    * @return string
    */
   public function getType(): string
   {
      return $this->type;
   }

   /**
    * Public and Private Keys.
    * @return array
    */
   public function getKeys(): array
   {
      return $this->keys;
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