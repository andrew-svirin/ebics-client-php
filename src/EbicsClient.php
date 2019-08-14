<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\EbicsBank;
use AndrewSvirin\Ebics\EbicsUser;
use AndrewSvirin\Ebics\Request;
use DOMDocument;

/**
 * EBICS client representation.
 */
class EbicsClient
{

    /**
     * An EbicsBank instance.
     * @var EbicsBank 
     */
    private $_bank;

    /**
     * An EbicsUser instance.
     * @var EbicsUser 
     */
    private $_user;

    /**
     * Constructor.
     * @param EbicsBank $bank
     * @param EbicsUser $user
     */
    public function __construct(EbicsBank $bank, EbicsUser $user)
    {
        $this->_bank = $bank;
        $this->_user = $user;
    }

    /**
     * Getter for bank.
     * @return EbicsBank
     */
    public function getBank()
    {
        return $this->_bank;
    }

    /**
     * Getter for user.
     * @return EbicsUser
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Downloads the bank account statement in SWIFT format (MT940).
     * @param timestamp $start The start date of requested transactions.
     * @param timestamp $end The end date of requested transactions.
     * @param boolean $parsed Flag whether the received MT940 message should be
     * parsed and returned as a dictionary or not.
     * @return
     */
    public function STA($start = NULL, $end = NULL, $parsed = FALSE)
    {
        return '';
    }

    /**
     * Downloads the interim transaction report in SWIFT format (MT942).
     * @param timestamp $start The start date of requested transactions.
     * @param timestamp $end The end date of requested transactions.
     * @param boolean $parsed Flag whether the received MT940 message should be
     * parsed and returned as a dictionary or not.
     * @return Response
     */
    public function VMK($start = NULL, $end = NULL, $parsed = FALSE)
    {
        $domTree = new DOMDocument();

        // Add OrderDetails.
        $xmlOrderDetails = $domTree->createElement('OrderDetails');
        $domTree->appendChild($xmlOrderDetails);

        // Add OrderType.
        $xmlOrderType = $domTree->createElement('OrderType');
        $xmlOrderType->nodeValue = 'VMK';
        $xmlOrderDetails->appendChild($xmlOrderType);

        // Add OrderAttribute.
        $xmlOrderAttribute = $domTree->createElement('OrderAttribute');
        $xmlOrderAttribute->nodeValue = 'DZHNN';
        $xmlOrderDetails->appendChild($xmlOrderAttribute);

        // Add StandardOrderParams.
        $xmlStandardOrderParams = $domTree->createElement('StandardOrderParams');
        $xmlOrderDetails->appendChild($xmlStandardOrderParams);

        if ($start != NULL && $end != NULL) {
            // Add DateRange.
            $xmlDateRange = $domTree->createElement('DateRange');
            $xmlStandardOrderParams->appendChild($xmlDateRange);

            // Add Start.
            $xmlStart = $domTree->createElement('Start');
            $xmlStart->nodeValue = $start;
            $xmlDateRange->appendChild($xmlStart);
            // Add End.
            $xmlEnd = $domTree->createElement('End');
            $xmlEnd->nodeValue = $end;
            $xmlDateRange->appendChild($xmlEnd);
        }

        $request = new Request($this);
        $orderDetails = $domTree->getElementsByTagName('OrderDetails')->item(0);

        return $request->createRequest($orderDetails)->download();
    }

}
