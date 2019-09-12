<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\handlers\traits\XPathTrait;
use AndrewSvirin\Ebics\services\CryptService;
use DOMDocument;
use DOMElement;

/**
 * Class AuthSignatureHandler manage body DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class AuthSignatureHandler
{

   use XPathTrait;

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
      $canonicalizationPath = '//AuthSignature/*';
      $signaturePath = "//*[@authenticate='true']";
      $signatureMethodAlgorithm = 'sha256';
      $digestMethodAlgorithm = 'sha256';
      $canonicalizationMethodAlgorithm = 'REC-xml-c14n-20010315';
      $digestTransformAlgorithm = 'REC-xml-c14n-20010315';

      // Add AuthSignature to request.
      $xmlAuthSignature = $xml->createElement('AuthSignature');
      $xmlRequest->appendChild($xmlAuthSignature);

      // Add ds:SignedInfo to AuthSignature.
      $xmlSignedInfo = $xml->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignedInfo');
      $xmlAuthSignature->appendChild($xmlSignedInfo);

      // Add ds:CanonicalizationMethod to ds:SignedInfo.
      $xmlCanonicalizationMethod = $xml->createElement('ds:CanonicalizationMethod');
      $xmlCanonicalizationMethod->setAttribute('Algorithm', sprintf('http://www.w3.org/TR/2001/%s', $canonicalizationMethodAlgorithm));
      $xmlSignedInfo->appendChild($xmlCanonicalizationMethod);

      // Add ds:SignatureMethod to ds:SignedInfo.
      $xmlSignatureMethod = $xml->createElement('ds:SignatureMethod');
      $xmlSignatureMethod->setAttribute('Algorithm', sprintf('http://www.w3.org/2001/04/xmldsig-more#rsa-%s', $signatureMethodAlgorithm));
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
      $xmlTransform->setAttribute('Algorithm', sprintf('http://www.w3.org/TR/2001/%s', $digestTransformAlgorithm));
      $xmlTransforms->appendChild($xmlTransform);

      // Add ds:DigestMethod to ds:Reference.
      $xmlDigestMethod = $xml->createElement('ds:DigestMethod');
      $xmlDigestMethod->setAttribute('Algorithm', sprintf('http://www.w3.org/2001/04/xmlenc#%s', $digestMethodAlgorithm));
      $xmlReference->appendChild($xmlDigestMethod);

      // Add ds:DigestValue to ds:Reference.
      $xmlDigestValue = $xml->createElement('ds:DigestValue');
      $canonicalizedHeader = $this->calculateC14N($xml, $signaturePath, $canonicalizationMethodAlgorithm);
      $canonicalizedHeaderHash = $this->cryptService->calculateHash($canonicalizedHeader, $digestMethodAlgorithm);
      $xmlDigestValue->nodeValue = base64_encode($canonicalizedHeaderHash);
      $xmlReference->appendChild($xmlDigestValue);

      // Add ds:SignatureValue to AuthSignature.
      $xmlSignatureValue = $xml->createElement('ds:SignatureValue');
      $canonicalizedSignedInfo = $this->calculateC14N($xml, $canonicalizationPath, $canonicalizationMethodAlgorithm);
      $canonicalizedSignedInfoHash = $this->cryptService->calculateHash($canonicalizedSignedInfo, $signatureMethodAlgorithm);
      $canonicalizedSignedInfoHashSigned = $this->cryptService->cryptSignatureValue($canonicalizedSignedInfoHash);
      $canonicalizedSignedInfoHashSignedEn = base64_encode($canonicalizedSignedInfoHashSigned);
      $xmlSignatureValue->nodeValue = $canonicalizedSignedInfoHashSignedEn;
      $xmlAuthSignature->appendChild($xmlSignatureValue);
   }

   /**
    * Extract C14N content by path from the XML DOM.
    * @param DOMDocument $xml
    * @param string $path
    * @param string $algo
    * @return string
    * @throws EbicsException
    */
   private function calculateC14N(DOMDocument $xml, string $path = '/', string $algo = 'REC-xml-c14n-20010315')
   {
      switch ($algo)
      {
         case 'REC-xml-c14n-20010315':
            $exclusive = false;
            $withComments = false;
            break;
         default:
            throw new EbicsException(sprintf('Define algo for %s', $algo));
      }
      $xpath = $this->prepareH004XPath($xml);
      $nodes = $xpath->query($path);
      $result = '';
      /* @var $node DOMElement */
      foreach ($nodes as $node)
      {
         $result .= $node->C14N($exclusive, $withComments);
      }
      return $result;
   }

}