<?php

namespace AndrewSvirin\Ebics\models;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use DOMDocument;
use DOMXPath;
use phpseclib\Crypt\RSA;

/**
 * Response model.
 */
class OldResponse
{

    /**
     * A Request instance.
     * @var Request 
     */
    private $request;

    /**
     * Constructor.
     * @param Request $request
     */
    public function __construct(Request $request = NULL)
    {
        $this->request = $request;
    }

    /**
     * Encription public key digest.
     * @var string base64 
     */
    public $encryptionPubKeyDigest;

    /**
     * Data (MT942)
     * @var string 
     */
    private $_mt942Data;

   /**
    * Set response.
    * @param string $source XML raw data.
    * @throws EbicsException
    */
    public function setResponse($source)
    {
        $domTree = new DOMDocument();
        $domTree->loadXML($source);
        $xpath = new DOMXPath($domTree);
        $xpath->registerNamespace("H004", 'urn:org:ebics:H004');

        $returnCode = $xpath->query('/H004:ebicsResponse/H004:body/H004:ReturnCode');
        $returnCodeValue = $returnCode->item(0)->nodeValue;
        if ($returnCodeValue !== '000000') {
            throw new EbicsException('EBICS response code: ' . $returnCode->item(0)->nodeValue);
        }

        $orderData = $xpath->query('/H004:ebicsResponse/H004:body/H004:DataTransfer/H004:OrderData');
        if ($orderData->length == 0) {
            throw new EbicsException('EBICS response empty result.');
        }
        $orderDataValue = $orderData->item(0)->nodeValue;

        $transactionId = $xpath->query('/H004:ebicsResponse/H004:body/H004:DataTransfer/H004:DataEncryptionInfo/H004:TransactionKey');
        $transactionIdValue = $transactionId->item(0)->nodeValue;
        $transactionIdBin = base64_decode($transactionIdValue);

        $encryptionPubKeyDigest = $xpath->query('/H004:ebicsResponse/H004:body/H004:DataTransfer/H004:DataEncryptionInfo/H004:EncryptionPubKeyDigest');
        $this->encryptionPubKeyDigest = $encryptionPubKeyDigest->item(0)->nodeValue;

        $rsa = new RSA();
        $rsa->setPassword($this->request->getClient()->getUser()->getKeyring()->getPassphrase());
        $rsa->loadKey($this->request->getClient()->getUser()->getEncriptionKey());
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $transactionIdDecoded = $rsa->decrypt($transactionIdBin);

        $decoded = openssl_decrypt($orderDataValue, "aes-128-cbc", $transactionIdDecoded, OPENSSL_ZERO_PADDING);
        $this->_mt942Data = gzuncompress($decoded);
    }

    /**
     * Set raw data.
     * @param string $mt942Data
     */
    public function setRawData($mt942Data)
    {
        $this->_mt942Data = $mt942Data;
    }

    /**
     * Get raw data.
     * @return string
     */
    public function getRawData()
    {
        return $this->_mt942Data;
    }

    /**
     * Getter for MT942.
     * @return MT942
     */
    public function getMT942()
    {
        return MT942::fromString($this->_mt942Data);
    }

    /**
     * Save to file.
     * @param string $filePath
     * @return integer|boolean
     */
    public function save($filePath)
    {
        return file_put_contents($filePath, $this->_mt942Data);
    }

}
