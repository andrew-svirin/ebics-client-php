<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\models\OrderDataEncrypted;
use AndrewSvirin\Ebics\services\CryptService;
use DOMDocument;
use DOMXPath;

/**
 * Class ResponseHandler manage response DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class ResponseHandler
{

   /**
    * Extract KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveKeyManagementResponseReturnCode(DOMDocument $xml)
   {
      $xpath = $this->prepareXPath($xml);
      $returnCodeNode = $xpath->query('/H004:ebicsKeyManagementResponse/H004:header/H004:mutable/H004:ReturnCode');
      $returnCode = $returnCodeNode->item(0)->nodeValue;
      return $returnCode;
   }

   /**
    * Extract KeyManagementResponse > header > mutable > ReportText value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveKeyManagementResponseReportText(DOMDocument $xml)
   {
      $xpath = $this->prepareXPath($xml);
      $returnReportText = $xpath->query('/H004:ebicsKeyManagementResponse/H004:header/H004:mutable/H004:ReportText');
      $returnReportText = $returnReportText->item(0)->nodeValue;
      return $returnReportText;
   }

   /**
    * Retrieve encoded order data.
    * @param DOMDocument $xml
    * @return OrderDataEncrypted
    * @throws EbicsException
    */
   public function retrieveKeyManagementResponseOrderData(DOMDocument $xml): OrderDataEncrypted
   {
      $xpath = $this->prepareXPath($xml);
      $orderData = $xpath->query('/H004:ebicsKeyManagementResponse/H004:body/H004:DataTransfer/H004:OrderData');
      $transactionId = $xpath->query('/H004:ebicsKeyManagementResponse/H004:body/H004:DataTransfer/H004:DataEncryptionInfo/H004:TransactionKey');
      if (!$orderData || !$transactionId)
      {
         throw new EbicsException('EBICS response empty result.');
      }
      $orderDataValue = $orderData->item(0)->nodeValue;
      $transactionIdValue = $transactionId->item(0)->nodeValue;
      $transactionIdValueDe = base64_decode($transactionIdValue);
      return new OrderDataEncrypted($orderDataValue, $transactionIdValueDe);
   }

   private function prepareXPath(DOMDocument $xml): DOMXPath
   {
      $xpath = new DomXpath($xml);
      $xpath->registerNamespace('H004', 'urn:org:ebics:H004');
      return $xpath;
   }
}