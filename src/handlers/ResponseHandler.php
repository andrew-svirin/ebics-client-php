<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\handlers\traits\XPathTrait;
use AndrewSvirin\Ebics\models\OrderDataEncrypted;
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
    * Extract KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveKeyManagementResponseReturnCode(DOMDocument $xml): string
   {
      $xpath = $this->prepareHXPath($xml);
      $returnCode = $xpath->query('/H004:ebicsKeyManagementResponse/H004:header/H004:mutable/H004:ReturnCode');
      $returnCodeValue = $returnCode->item(0)->nodeValue;
      return $returnCodeValue;
   }

   /**
    * Extract KeyManagementResponse > header > mutable > ReportText value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveKeyManagementResponseReportText(DOMDocument $xml): string
   {
      $xpath = $this->prepareHXPath($xml);
      $reportText = $xpath->query('/H004:ebicsKeyManagementResponse/H004:header/H004:mutable/H004:ReportText');
      $reportTextValue = $reportText->item(0)->nodeValue;
      return $reportTextValue;
   }

   /**
    * Retrieve encoded order data.
    * @param DOMDocument $xml
    * @return OrderDataEncrypted
    * @throws EbicsException
    */
   public function retrieveKeyManagementResponseOrderData(DOMDocument $xml): OrderDataEncrypted
   {
      $xpath = $this->prepareHXPath($xml);
      $orderData = $xpath->query('/H004:ebicsKeyManagementResponse/H004:body/H004:DataTransfer/H004:OrderData');
      $transactionKey = $xpath->query('/H004:ebicsKeyManagementResponse/H004:body/H004:DataTransfer/H004:DataEncryptionInfo/H004:TransactionKey');
      if (!$orderData || !$transactionKey)
      {
         throw new EbicsException('EBICS response empty result.');
      }
      $orderDataValue = $orderData->item(0)->nodeValue;
      $transactionKeyValue = $transactionKey->item(0)->nodeValue;
      $transactionKeyValueDe = base64_decode($transactionKeyValue);
      return new OrderDataEncrypted($orderDataValue, $transactionKeyValueDe);
   }

}