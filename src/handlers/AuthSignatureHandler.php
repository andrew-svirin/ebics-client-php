<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\services\CryptService;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Class AuthSignatureHandler manage body DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class AuthSignatureHandler
{

   /**
    * @var CryptService
    */
   private $cryptService;

   public function __construct(CryptService $cryptService)
   {
      $this->cryptService = $cryptService;
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
      $canonicalizedHeader = $this->calculateC14N($xml, $signaturePath);
      $canonicalizedHeaderHash = $this->cryptService->calculateHash($canonicalizedHeader);
      $xmlDigestValue->nodeValue = base64_encode($canonicalizedHeaderHash);
      $xmlReference->appendChild($xmlDigestValue);

      // Add ds:SignatureValue to AuthSignature.
      $xmlSignatureValue = $xml->createElement('ds:SignatureValue');
      $canonicalizedSignedInfo = $xmlSignedInfo->C14N();
      $canonicalizedSignedInfoHash = $this->cryptService->calculateHash($canonicalizedSignedInfo);
      $canonicalizedSignedInfoHashSigned = $this->cryptService->cryptSignatureValue($canonicalizedSignedInfoHash);
      $canonicalizedSignedInfoHashSignedEn = base64_encode($canonicalizedSignedInfoHashSigned);
      $xmlSignatureValue->nodeValue = $canonicalizedSignedInfoHashSignedEn;
      $xmlAuthSignature->appendChild($xmlSignatureValue);
   }

   /**
    * Extract C14N content by path from the XML DOM.
    * @param DOMDocument $xml
    * @param $path
    * @return string
    */
   private function calculateC14N(DOMDocument $xml, $path)
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

}