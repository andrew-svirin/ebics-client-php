<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\models\KeyRing;
use DOMDocument;
use DOMElement;
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
    * @param DOMElement $xmlHeader
    * @throws EbicsException
    */
   public function handle(DOMDocument $xml, DOMElement $xmlRequest, DOMElement $xmlHeader)
   {
      // Add AuthSignature to request.
      $xmlAuthSignature = $xml->createElement('AuthSignature');
      $xmlRequest->appendChild($xmlAuthSignature);

      // Add ds:SignedInfo to AuthSignature.
      $xmlSignedInfo = $xml->createElement('ds:SignedInfo');
      $xmlSignedInfo->setAttribute('xmlns', 'http://www.w3.org/2000/09/xmldsig#');
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
      $xmlReference->setAttribute('URI', "#xpointer(//*[@authenticate='true'])");
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
      $canonicalizedHeader = $xmlHeader->C14N();
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
    * Calculate signatureValue.
    * @param string $hash
    * @return string Base64 encoded
    * @throws EbicsException
    */
   private function calculateSignatureValue(string $hash): string
   {
      $privateKey = $this->keyRing->getCertificateX()->getKeys()['privatekey'];
      $publicKey = $this->keyRing->getCertificateX()->getKeys()['publickey'];
      $passphrase = $this->keyRing->getPassword();

      $rsa = new RSA();
      $rsa->setPassword($passphrase);
      $rsa->loadKey($privateKey, RSA::PRIVATE_FORMAT_PKCS1);
//      $rsa->setPrivateKey();
//      $rsa = new RSA();
//      $rsa->loadKey($pk);
      $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
      $signed = $rsa->sign($hash);

      $rsa->loadKey($publicKey); // public key
      $v =  $rsa->verify($hash, $signed) ? 'verified' : 'unverified';
      return $signed;

      $resX = openssl_get_privatekey($privateKey, $passphrase);
//      openssl_sign($hash,$signature,$resX,'sha256WithRSAEncryption');
//      return $signature;
      // $signature = base64_encode($signature);

      $RSA_SHA256prefix = [
         0x30, 0x31, 0x30, 0x0D, 0x06, 0x09, 0x60, 0x86, 0x48, 0x01, 0x65, 0x03, 0x04, 0x02, 0x01, 0x05, 0x00, 0x04, 0x20,
      ];
      $signedInfoDigest = array_values(unpack('C*', $hash));
      $digestToSign = [];
      $this->systemArrayCopy($RSA_SHA256prefix, 0, $digestToSign, 0, count($RSA_SHA256prefix));
      $this->systemArrayCopy($signedInfoDigest, 0, $digestToSign, count($RSA_SHA256prefix), count($signedInfoDigest));
      $digestToSignBin = $this->arrayToBin($digestToSign);
//      $privateKey = $this->_client->getUser()->getAuthorizationKey();
//      $passphrase = $this->_client->getUser()->getKeyring()->getPassphrase();
//      $resX = openssl_get_privatekey($privateKey, $passphrase);
//      $certA = $this->keyRing->getCertificateA()->toX509();
//      $privateKey = $this->keyRing->getCertificateX()->getKeys()['privatekey'];
//      $passphrase = $this->keyRing->getPassword();
//      $resX = openssl_get_privatekey($privateKey, $passphrase);
//      $resX = $privateKey;
      if ($resX == FALSE)
      {
         throw new EbicsException('Incorrect private key and passphrase.');
      }
      $sign = NULL;
      openssl_private_encrypt($digestToSignBin, $sign, $resX);
      return $sign;
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