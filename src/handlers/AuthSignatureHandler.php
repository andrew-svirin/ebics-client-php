<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\models\KeyRing;
use DOMDocument;
use DOMElement;
use DOMXPath;
use phpseclib\Crypt\RSA;

/**
 * Class AuthSignatureHandler manage body DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class AuthSignatureHandler
{

   /**
    * @var KeyRing
    */
   private $keyRing;

   public function __construct(KeyRing $keyRing)
   {
      $this->keyRing = $keyRing;
   }

   /**
    * Add body and children elements to request.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @throws EbicsException
    */
   public function handle(DOMDocument $xml, DOMElement $xmlRequest)
   {
      $signaturePath = "//*[@authenticate='true']";
      // Add AuthSignature to request.
      $xmlAuthSignature = $xml->createElement('AuthSignature');
      $xmlRequest->appendChild($xmlAuthSignature);

      // Add ds:SignedInfo to AuthSignature.
      $xmlSignedInfo = $xml->createElement('ds:SignedInfo');
      //$xmlSignedInfo->setAttribute('xmlns', 'http://www.w3.org/2000/09/xmldsig#');
      $xmlAuthSignature->appendChild($xmlSignedInfo);

      // Add ds:CanonicalizationMethod to ds:SignedInfo.
      $xmlCanonicalizationMethod = $xml->createElement('ds:CanonicalizationMethod');
      $xmlCanonicalizationMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
      $xmlSignedInfo->appendChild($xmlCanonicalizationMethod);

      // Add ds:SignatureMethod to ds:SignedInfo.
      $xmlSignatureMethod = $xml->createElement('ds:SignatureMethod');
      $xmlSignatureMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
      $xmlSignedInfo->appendChild($xmlSignatureMethod);

      // Add ds:Reference to ds:SignedInfo.
      $xmlReference = $xml->createElement('ds:Reference');
      $xmlReference->setAttribute('URI', sprintf('#xpointer(%s)', $signaturePath));
      $xmlSignedInfo->appendChild($xmlReference);

      // Add ds:Transforms to ds:Reference.
      $xmlTransforms = $xml->createElement('ds:Transforms');
      $xmlReference->appendChild($xmlTransforms);

      // Add ds:Transform to ds:Transforms.
      $xmlTransform = $xml->createElement('ds:Transform');
      $xmlTransform->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
      $xmlTransforms->appendChild($xmlTransform);

      // Add ds:DigestMethod to ds:Reference.
      $xmlDigestMethod = $xml->createElement('ds:DigestMethod');
      $xmlDigestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
      $xmlReference->appendChild($xmlDigestMethod);

      // Add ds:DigestValue to ds:Reference.
      $xmlDigestValue = $xml->createElement('ds:DigestValue');
      $canonicalizedHeader = $this->calculateC14Content($xml, $signaturePath);
      $canonicalizedHeaderHash = hash('SHA256', $canonicalizedHeader, true);
      $xmlDigestValue->nodeValue = base64_encode($canonicalizedHeaderHash);
      $xmlReference->appendChild($xmlDigestValue);

      // Add ds:SignatureValue to AuthSignature.
      $xmlSignatureValue = $xml->createElement('ds:SignatureValue');
      $canonicalizedSignedInfo = $xmlSignedInfo->C14N();
      $canonicalizedSignedInfoHash = hash('SHA256', $canonicalizedSignedInfo, true);
      $canonicalizedSignedInfoHashSigned = $this->calculateSignatureValue($canonicalizedSignedInfoHash);
      $canonicalizedSignedInfoHashSignedEn = base64_encode($canonicalizedSignedInfoHashSigned);
      $xmlSignatureValue->nodeValue = $canonicalizedSignedInfoHashSignedEn;
      $xmlAuthSignature->appendChild($xmlSignatureValue);
   }

   /**
    * Extract C14 content by path from the XML DOM.
    * @param DOMDocument $xml
    * @param $path
    * @return string
    */
   private function calculateC14Content(DOMDocument $xml, $path)
   {
      $xpath = new DOMXPath($xml);
      $nodes = $xpath->query($path);
      $result = '';
      /* @var $node DOMElement */
      foreach ($nodes as $node)
      {
         $result .= $node->C14N();
      }
      return $result;
   }

   /**
    * Calculate Public Digest
    *
    * Concat the exponent and modulus (hex representation) with a single whitespace
    * remove leading zeros from both
    * calculate digest (SHA256)
    * encode as Base64
    *
    * @param integer $exponent
    * @param integer $modulus
    * @return string
    */
   public function calculateDigest($exponent, $modulus)
   {
      $e = ltrim((string)$exponent, '0');
      $m = ltrim((string)$modulus, '0');
      $concat = $e . ' ' . $m;
      $sha256 = hash('sha256', $concat, TRUE);
      $b64en = base64_encode($sha256);
      return $b64en;
   }

   /**
    * Calculate signatureValue by encrypting Signature value with user Private key.
    * @param string $hash
    * @return string Base64 encoded
    * @throws EbicsException
    */
   private function calculateSignatureValue(string $hash): string
   {
      $digestToSignBin = $this->filter($hash);
      $privateKey = $this->keyRing->getUserCertificateX()->getKeys()['privatekey'];
      $passphrase = $this->keyRing->getPassword();
      $rsa = new RSA();
      $rsa->setPassword($passphrase);
      $rsa->loadKey($privateKey, RSA::PRIVATE_FORMAT_PKCS1);
      define('CRYPT_RSA_PKCS15_COMPAT', true);
      $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
      $encrypted = $rsa->encrypt($digestToSignBin);
      if (empty($encrypted))
      {
         throw new EbicsException('Incorrect authorization.');
      }
      return $encrypted;
   }

   /**
    * Filter hash of blocked characters.
    * @param string $hash
    * @return string
    */
   private function filter(string $hash)
   {
      $RSA_SHA256prefix = [
         0x30, 0x31, 0x30, 0x0D, 0x06, 0x09, 0x60, 0x86, 0x48, 0x01, 0x65, 0x03, 0x04, 0x02, 0x01, 0x05, 0x00, 0x04, 0x20,
      ];
      $signedInfoDigest = array_values(unpack('C*', $hash));
      $digestToSign = [];
      $this->systemArrayCopy($RSA_SHA256prefix, 0, $digestToSign, 0, count($RSA_SHA256prefix));
      $this->systemArrayCopy($signedInfoDigest, 0, $digestToSign, count($RSA_SHA256prefix), count($signedInfoDigest));
      $digestToSignBin = $this->arrayToBin($digestToSign);
      return $digestToSignBin;
   }

   /**
    * System.arrayCopy java function interpretation.
    * @param array $a
    * @param integer $c
    * @param array $b
    * @param integer $d
    * @param integer $length
    */
   private function systemArrayCopy(array $a, int $c, array &$b, int $d, int $length): void
   {
      for ($i = 0; $i < $length; $i++)
      {
         $b[$i + $d] = $a[$i + $c];
      }
   }

   /**
    * Pack array of bytes to one bytes-string.
    * @param array $bytes
    * @return string (bytes)
    */
   private function arrayToBin(array $bytes): string
   {
      return call_user_func_array('pack', array_merge(['c*'], $bytes));
   }

}