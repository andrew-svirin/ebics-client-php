<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\factories\TransactionFactory;
use AndrewSvirin\Ebics\handlers\traits\XPathTrait;
use AndrewSvirin\Ebics\models\OrderDataEncrypted;
use AndrewSvirin\Ebics\models\Transaction;
use DOMDocument;

/**
 * Class ResponseHandler manage response DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class ResponseHandler
{

   use XPathTrait;

   /**
    * Extract H004 > KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveH004ReturnCode(DOMDocument $xml): string
   {
      $xpath = $this->prepareH004XPath($xml);
      $returnCode = $xpath->query('//H004:header/H004:mutable/H004:ReturnCode');
      $returnCodeValue = $returnCode->item(0)->nodeValue;
      return $returnCodeValue;
   }

   /**
    * Extract H004 > KeyManagementResponse > header > mutable > ReportText value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveH004ReportText(DOMDocument $xml): string
   {
      $xpath = $this->prepareH004XPath($xml);
      $reportText = $xpath->query('//H004:header/H004:mutable/H004:ReportText');
      $reportTextValue = $reportText->item(0)->nodeValue;
      return $reportTextValue;
   }

   /**
    * Extract H000 > SystemReturnCode > ReturnCode value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveH000ReturnCode(DOMDocument $xml): string
   {
      $xpath = $this->prepareH000XPath($xml);
      $returnCode = $xpath->query('//H000:SystemReturnCode/H000:ReturnCode');
      $returnCodeValue = $returnCode->item(0)->nodeValue;
      return $returnCodeValue;
   }

   /**
    * Extract H000 > SystemReturnCode > ReportText value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveH000ReportText(DOMDocument $xml): string
   {
      $xpath = $this->prepareH000XPath($xml);
      $reportText = $xpath->query('//H000:SystemReturnCode/H000:ReportText');
      $reportTextValue = $reportText->item(0)->nodeValue;
      return $reportTextValue;
   }

   /**
    * Retrieve encoded Order data.
    * @param DOMDocument $xml
    * @return OrderDataEncrypted
    * @throws EbicsException
    */
   public function retrieveOrderData(DOMDocument $xml): OrderDataEncrypted
   {
      $xpath = $this->prepareH004XPath($xml);
      $orderData = $xpath->query('//H004:body/H004:DataTransfer/H004:OrderData');
      $transactionKey = $xpath->query('//H004:body/H004:DataTransfer/H004:DataEncryptionInfo/H004:TransactionKey');
      if (!$orderData || !$transactionKey)
      {
         throw new EbicsException('EBICS response empty result.');
      }
      $orderDataValue = $orderData->item(0)->nodeValue;
      $transactionKeyValue = $transactionKey->item(0)->nodeValue;
      $transactionKeyValueDe = base64_decode($transactionKeyValue);
      return new OrderDataEncrypted($orderDataValue, $transactionKeyValueDe);
   }

   /**
    * Extract Transaction from the DOM XML.
    * @param DOMDocument $xml
    * @return Transaction
    */
   public function retrieveTransaction(DOMDocument $xml): Transaction
   {
      $c = $xml->C14N();
      $xpath = $this->prepareH004XPath($xml);
      $transactionId = $xpath->query('//H004:header/H004:static/H004:TransactionID');
      $transactionIdValue = $transactionId->item(0)->nodeValue;
      $numSegments = $xpath->query('//H004:header/H004:static/H004:NumSegments');
      $numSegmentsValue = $numSegments->item(0)->nodeValue;
      $transactionPhase = $xpath->query('//H004:header/H004:mutable/H004:TransactionPhase');
      $transactionPhaseValue = $transactionPhase->item(0)->nodeValue;
      $segmentNumber = $xpath->query('//H004:header/H004:mutable/H004:SegmentNumber');
      $segmentNumberValue = $segmentNumber->item(0)->nodeValue;
      return TransactionFactory::buildTransaction($transactionIdValue, $transactionPhaseValue, $numSegmentsValue, $segmentNumberValue);
   }
}