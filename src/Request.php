<?php

namespace Ukrinsoft\Ebics;

use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMNode;
use Comodojo\Httprequest\Httprequest;
use Ukrinsoft\Ebics\EbicsClient;
use Ukrinsoft\Ebics\Response;
use Exception;

/**
 * Request model.
 */
class Request
{

    /**
     * Request DOMTree.
     * @var DOMDocument 
     */
    private $_request;

    /**
     * An EbicsClient instance.
     * @var EbicsClient 
     */
    private $_client;

    /**
     * Constructor.
     * @param EbicsClient $client
     */
    public function __construct(EbicsClient $client)
    {
        $this->_client = $client;
    }

    /**
     * Getter for {client}.
     * @return EbicsClient
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Create request.
     * @param DOMNode $xmlOrderDetails
     * @return Request
     */
    public function createRequest($xmlOrderDetails)
    {
        $domTree = new DOMDocument('1.0', 'utf-8');

        // Add ebicsRequest.
        $xmlEbicsRequest = $domTree->createElementNS('urn:org:ebics:H004', 'ebicsRequest');
        $xmlEbicsRequest->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        $xmlEbicsRequest->setAttribute('Version', 'H004');
        $xmlEbicsRequest->setAttribute('Revision', '1');
        $domTree->appendChild($xmlEbicsRequest);

        // Add header.
        $xmlHeader = $domTree->createElement('header');
        $xmlHeader->setAttribute('authenticate', 'true');
        $xmlEbicsRequest->appendChild($xmlHeader);

        // Add static.
        $xmlStatic = $domTree->createElement('static');
        $xmlHeader->appendChild($xmlStatic);

        // Add HostID.
        $xmlHostId = $domTree->createElement('HostID');
        $xmlHostId->nodeValue = $this->_client->getBank()->getHostId();
        $xmlStatic->appendChild($xmlHostId);

        // Add Nonce.
        $xmlNonce = $domTree->createElement('Nonce');
        $xmlNonce->nodeValue = $this->_getNonce();
        $xmlStatic->appendChild($xmlNonce);

        // Add Timestamp.
        $xmlTimestamp = $domTree->createElement('Timestamp');
        $xmlTimestamp->nodeValue = $this->_getUTCTimestamp();
        $xmlStatic->appendChild($xmlTimestamp);

        // Add PartnerID.
        $xmlPartnerID = $domTree->createElement('PartnerID');
        $xmlPartnerID->nodeValue = $this->_client->getUser()->getPartnerId();
        $xmlStatic->appendChild($xmlPartnerID);

        // Add UserID.
        $xmlUserID = $domTree->createElement('UserID');
        $xmlUserID->nodeValue = $this->_client->getUser()->getUserId();
        $xmlStatic->appendChild($xmlUserID);

        // Add Product.
        $xmlProduct = $domTree->createElement('Product');
        $xmlProduct->setAttribute('Language', 'de');
        $xmlProduct->nodeValue = 'EventAgent24.';
        $xmlStatic->appendChild($xmlProduct);

        // Add OrderDetails.
        $xmlOD = $domTree->importNode($xmlOrderDetails, true);
        $xmlStatic->appendChild($xmlOD);

        // Add BankPubKeyDigests.
        $xmlBankPubKeyDigests = $domTree->createElement('BankPubKeyDigests');
        $xmlStatic->appendChild($xmlBankPubKeyDigests);

        // Add Authentication.
        $xmlAuthentication = $domTree->createElement('Authentication');
        $xmlAuthentication->setAttribute('Version', $this->_client->getBank()->getBankAuthenticationKeyVersion());
        $xmlAuthentication->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $xmlAuthentication->nodeValue = $this->_client->getBank()->getBankAuthenticationPublicDigest();
        $xmlBankPubKeyDigests->appendChild($xmlAuthentication);

        // Add Encryption.
        $xmlEncryption = $domTree->createElement('Encryption');
        $xmlEncryption->setAttribute('Version', $this->_client->getBank()->getBankEncryptionKeyVersion());
        $xmlEncryption->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $xmlEncryption->nodeValue = $this->_client->getBank()->getBankEncryptionPublicDigest();
        $xmlBankPubKeyDigests->appendChild($xmlEncryption);

        // Add SecurityMedium.
        $xmlSecurityMedium = $domTree->createElement('SecurityMedium');
        $xmlSecurityMedium->nodeValue = '0000';
        $xmlStatic->appendChild($xmlSecurityMedium);

        // Add mutable.
        $xmlMutable = $domTree->createElement('mutable');
        $xmlHeader->appendChild($xmlMutable);

        // Add TransactionPhase.
        $xmlTransactionPhase = $domTree->createElement('TransactionPhase');
        $xmlTransactionPhase->nodeValue = 'Initialisation';
        $xmlMutable->appendChild($xmlTransactionPhase);

        // Add AuthSignature.
        $xmlAuthSignature = $domTree->createElement('AuthSignature');
        $xmlEbicsRequest->appendChild($xmlAuthSignature);

        // Add ds:SignedInfo.
        $xmlSignedInfo = $domTree->createElement('ds:SignedInfo');
        $xmlAuthSignature->appendChild($xmlSignedInfo);

        // Add ds:CanonicalizationMethod.
        $xmlCanonicalizationMethod = $domTree->createElement('ds:CanonicalizationMethod');
        $xmlCanonicalizationMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $xmlSignedInfo->appendChild($xmlCanonicalizationMethod);

        // Add ds:SignatureMethod.
        $xmlSignatureMethod = $domTree->createElement('ds:SignatureMethod');
        $xmlSignatureMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
        $xmlSignedInfo->appendChild($xmlSignatureMethod);

        // Add ds:Reference.
        $xmlReference = $domTree->createElement('ds:Reference');
        $xmlReference->setAttribute('URI', '#xpointer(//*[@authenticate=\'true\'])');
        $xmlSignedInfo->appendChild($xmlReference);

        // Add ds:Transforms.
        $xmlTransforms = $domTree->createElement('ds:Transforms');
        $xmlReference->appendChild($xmlTransforms);

        // Add ds:Transform.
        $xmlTransform = $domTree->createElement('ds:Transform');
        $xmlTransform->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $xmlTransforms->appendChild($xmlTransform);

        // Add ds:DigestMethod.
        $xmlDigestMethod = $domTree->createElement('ds:DigestMethod');
        $xmlDigestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $xmlReference->appendChild($xmlDigestMethod);

        // Add ds:DigestValue.
        $xmlDigestValue = $domTree->createElement('ds:DigestValue');
        $xmlReference->appendChild($xmlDigestValue);

        // Add ds:SignatureValue.
        $xmlSignatureValue = $domTree->createElement('ds:SignatureValue');
        $xmlAuthSignature->appendChild($xmlSignatureValue);

        // Add body.
        $xmlBody = $domTree->createElement('body');
        $xmlEbicsRequest->appendChild($xmlBody);

        $canonicalizedHeader = $xmlHeader->C14N();
        $digestValue = base64_encode(hash('SHA256', $canonicalizedHeader, true));
        $xmlDigestValue->nodeValue = $digestValue;

        // Need to sign document.
        $canonicalizedSignedInfo = $xmlSignedInfo->C14N();
        $signatureValue = $this->_getSignatureValue(base64_encode(hash('SHA256', $canonicalizedSignedInfo, true)));
        $xmlSignatureValue->nodeValue = $signatureValue;

        $this->_request = $domTree;

        return $this;
    }

    /**
     * Getter for {nonce}.
     * @return string HEX
     */
    private function _getNonce()
    {
        $bytes = openssl_random_pseudo_bytes(16);
        $nonce = bin2hex($bytes);
        $nonceUpper = strtoupper($nonce);

        return $nonceUpper;
    }

    /**
     * Getter for {UTCTimestamp}.
     * Timestamp in UTC.
     * @return string (ISO 8601)
     */
    private function _getUTCTimestamp()
    {
        $time = time();
        $date = new DateTime();
        $date->setTimestamp($time);
        $date->setTimezone(new DateTimeZone('Europe/Berlin'));

        return $date->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Getter for {signatureValue}.
     * @param string $sign64e Base64 encoded
     * @return string Base64 encoded
     */
    private function _getSignatureValue($sign64e)
    {
        $RSA_SHA256prefix = [
            0x30, 0x31, 0x30, 0x0D, 0x06, 0x09, 0x60, 0x86, 0x48, 0x01, 0x65, 0x03, 0x04, 0x02, 0x01, 0x05, 0x00, 0x04, 0x20,
        ];

        $signedInfoDigest = array_values(unpack('C*', base64_decode($sign64e)));
        $digestToSign = [];
        self::_systemArrayCopy($RSA_SHA256prefix, 0, $digestToSign, 0, count($RSA_SHA256prefix));
        self::_systemArrayCopy($signedInfoDigest, 0, $digestToSign, count($RSA_SHA256prefix), count($signedInfoDigest));
        $digestToSignBin = self::_arrayToBin($digestToSign);
        $privateKey = $this->_client->getUser()->getAuthorizationKey();
        $passphrase = $this->_client->getUser()->getKeyring()->getPassphrase();
        $resX = openssl_get_privatekey($privateKey, $passphrase);
        if ($resX == FALSE) {
            throw new Exception('Incorrect private key and passphrase.');
        }

        $sign = NULL;
        openssl_private_encrypt($digestToSignBin, $sign, $resX);
        $signatureValue64e = base64_encode($sign);

        return $signatureValue64e;
    }

    /**
     * System.arrayCopy java function
     * @param array $a
     * @param integer $c
     * @param array $b
     * @param integer $d
     * @param integer $length
     */
    private static function _systemArrayCopy(array $a, $c, array &$b, $d, $length)
    {
        for ($i = 0; $i < $length; $i++) {
            $b[$i + $d] = $a[$i + $c];
        }
    }

    /**
     * Pack array of bytes to one bytes-string.
     * @param type $bytes
     * @return string (bytes)
     */
    private static function _arrayToBin(array $bytes)
    {
        return call_user_func_array("pack", array_merge(array("c*"), $bytes));
    }

    /**
     * Download.
     * @return Response
     */
    public function download($data = NULL)
    {
        if ($data == NULL) {
            $domTree = $this->_request;
            $requestBody = $domTree->saveXML();

            $request = new Httprequest($this->_client->getBank()->getUrl(), false);
            $data = $request
                ->setHttpMethod("POST")
                ->setContentType("text/xml; charset=ISO-8859-1")
                ->send($requestBody);
        }
        $response = new Response($this);
        $response->setResponse($data);

        return $response;
    }

}
