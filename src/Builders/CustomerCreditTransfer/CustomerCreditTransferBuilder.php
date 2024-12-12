<?php

namespace EbicsApi\Ebics\Builders\CustomerCreditTransfer;

use EbicsApi\Ebics\Contracts\PostalAddressInterface;
use EbicsApi\Ebics\Handlers\Traits\XPathTrait;
use EbicsApi\Ebics\Models\CustomerCreditTransfer;
use EbicsApi\Ebics\Services\DOMHelper;
use EbicsApi\Ebics\Services\RandomService;
use DateTime;
use DOMElement;
use InvalidArgumentException;

/**
 * Class CustomerCreditTransferBuilder builder for model @see \EbicsApi\Ebics\Models\CustomerCreditTransfer
 *
 * https://www.six-group.com/dam/download/banking-services/interbank-clearing/en/standardization/iso/swiss-recommendations/implementation-guidelines-ct.pdf
 *  https://www.six-group.com/dam/download/banking-services/interbank-clearing/de/standardization/iso/swiss-recommendations/archives/implementation-guidelines-ct/implementation-guidelines-ct_v1_6_1.pdf
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 * @author Jonathan Luthi
 */
final class CustomerCreditTransferBuilder
{
    use XPathTrait;

    private RandomService $randomService;
    private ?CustomerCreditTransfer $instance;

    public function __construct()
    {
        $this->randomService = new RandomService();
    }

    /**
     * @param string $schema namespace schema urn:iso:std:iso:20022:tech:xsd:pain.001.001.03
     * @param string $debitorFinInstBIC
     * @param string $debitorIBAN
     * @param string $debitorName
     * @param DateTime|null $executionDate
     * @param bool $batchBooking By deactivating the batch booking procedure,
     * you request your credit institution to book each transaction within this order separately.
     * @param string|null $msgId Overwrite default generated message id - should be unique at
     * least for 15 days. Used for rejecting duplicated transactions (max length: 35 characters)
     * @param string|null $paymentReference Overwrite default payment reference -
     * visible on creditors bank statement (max length: 35 characters)
     *
     * @return $this
     */
    public function createInstance(
        string $schema,
        string $debitorFinInstBIC,
        string $debitorIBAN,
        string $debitorName,
        DateTime $executionDate = null,
        bool $batchBooking = true,
        string $msgId = null,
        string $paymentReference = null
    ): CustomerCreditTransferBuilder {
        $this->instance = new CustomerCreditTransfer();
        $now = new DateTime();

        $xmDocument = $this->instance->createElementNS(
            $schema,
            'Document'
        );
        $xmDocument->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $xmDocument->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03 pain.001.001.03.xsd'
        );
        $this->instance->appendChild($xmDocument);

        $xmlCstmrCdtTrfInitn = $this->instance->createElement('CstmrCdtTrfInitn');
        $xmDocument->appendChild($xmlCstmrCdtTrfInitn);

        $xmlGrpHdr = $this->instance->createElement('GrpHdr');
        $xmlCstmrCdtTrfInitn->appendChild($xmlGrpHdr);

        $xmlMsgId = $this->instance->createElement('MsgId');
        if ($msgId) {
            $xmlMsgId->nodeValue = $msgId;
        } else {
            $xmlMsgId->nodeValue = $this->randomService->uniqueIdWithDate('msg');
        }
        $xmlGrpHdr->appendChild($xmlMsgId);

        $xmlMsgId = $this->instance->createElement('CreDtTm');
        $xmlMsgId->nodeValue = $now->format('Y-m-d\TH:i:s\.vP');
        $xmlGrpHdr->appendChild($xmlMsgId);

        $xmlNbOfTxs = $this->instance->createElement('NbOfTxs');
        $xmlNbOfTxs->nodeValue = '0';
        $xmlGrpHdr->appendChild($xmlNbOfTxs);

        $xmlCtrlSum = $this->instance->createElement('CtrlSum');
        $xmlCtrlSum->nodeValue = '0';
        $xmlGrpHdr->appendChild($xmlCtrlSum);

        $xmlInitgPty = $this->instance->createElement('InitgPty');
        $xmlGrpHdr->appendChild($xmlInitgPty);

        $xmlNm = $this->instance->createElement('Nm');
        $xmlNm->nodeValue = $debitorName;
        $xmlInitgPty->appendChild($xmlNm);

        $xmlPmtInf = $this->instance->createElement('PmtInf');
        $xmlCstmrCdtTrfInitn->appendChild($xmlPmtInf);

        $xmlPmtInfId = $this->instance->createElement('PmtInfId');
        if ($paymentReference) {
            $xmlPmtInfId->nodeValue = $paymentReference;
        } else {
            $xmlPmtInfId->nodeValue = $this->randomService->uniqueIdWithDate('pmt');
        }
        $xmlPmtInf->appendChild($xmlPmtInfId);

        $xmlPmtMtd = $this->instance->createElement('PmtMtd');
        $xmlPmtMtd->nodeValue = 'TRF';
        $xmlPmtInf->appendChild($xmlPmtMtd);

        $xmlBtchBookg = $this->instance->createElement('BtchBookg');
        $xmlBtchBookg->nodeValue = $batchBooking ? 'true' : 'false';
        $xmlPmtInf->appendChild($xmlBtchBookg);

        $xmlNbOfTxs = $this->instance->createElement('NbOfTxs');
        $xmlNbOfTxs->nodeValue = '0';
        $xmlPmtInf->appendChild($xmlNbOfTxs);

        $xmlCtrlSum = $this->instance->createElement('CtrlSum');
        $xmlCtrlSum->nodeValue = '0';
        $xmlPmtInf->appendChild($xmlCtrlSum);

        $xmlPmtTpInf = $this->instance->createElement('PmtTpInf');
        $xmlPmtInf->appendChild($xmlPmtTpInf);

        $xmlSvcLvl = $this->instance->createElement('SvcLvl');
        $xmlPmtTpInf->appendChild($xmlSvcLvl);

        $xmlCd = $this->instance->createElement('Cd');
        $xmlCd->nodeValue = 'SEPA';
        $xmlSvcLvl->appendChild($xmlCd);

        $xmlReqdExctnDt = $this->instance->createElement('ReqdExctnDt');
        if ($executionDate) {
            $xmlReqdExctnDt->nodeValue = $executionDate->format('Y-m-d');
        } else {
            $xmlReqdExctnDt->nodeValue = $now->format('Y-m-d');
        }
        $xmlPmtInf->appendChild($xmlReqdExctnDt);

        $xmlDbtr = $this->instance->createElement('Dbtr');
        $xmlPmtInf->appendChild($xmlDbtr);

        $xmlNm = $this->instance->createElement('Nm');
        $xmlNm->nodeValue = $debitorName;
        $xmlDbtr->appendChild($xmlNm);

        $xmlDbtrAcct = $this->instance->createElement('DbtrAcct');
        $xmlPmtInf->appendChild($xmlDbtrAcct);

        $xmlId = $this->instance->createElement('Id');
        $xmlDbtrAcct->appendChild($xmlId);

        $xmlIBAN = $this->instance->createElement('IBAN');
        $xmlIBAN->nodeValue = $debitorIBAN;
        $xmlId->appendChild($xmlIBAN);

        $xmlDbtrAgt = $this->instance->createElement('DbtrAgt');
        $xmlPmtInf->appendChild($xmlDbtrAgt);

        $xmlFinInstnId = $this->instance->createElement('FinInstnId');
        $xmlDbtrAgt->appendChild($xmlFinInstnId);

        $xmlBIC = $this->instance->createElement('BIC');
        $xmlBIC->nodeValue = $debitorFinInstBIC;
        $xmlFinInstnId->appendChild($xmlBIC);

        $xmlChrgBr = $this->instance->createElement('ChrgBr');
        $xmlChrgBr->nodeValue = 'SLEV';
        $xmlPmtInf->appendChild($xmlChrgBr);

        return $this;
    }

    private function createCreditTransferTransactionElement(
        float $amount,
        string $instrId = null,
        string $endToEndId = null
    ): DOMElement {
        $xpath = $this->prepareXPath($this->instance);
        $nbOfTxsList = $xpath->query('//CstmrCdtTrfInitn//GrpHdr/NbOfTxs');
        $nbOfTxs = (int)DOMHelper::safeItemValue($nbOfTxsList);
        $nbOfTxs++;

        $pmtInfList = $xpath->query('//CstmrCdtTrfInitn/PmtInf');
        $xmlPmtInf = DOMHelper::safeItem($pmtInfList);

        $xmlCdtTrfTxInf = $this->instance->createElement('CdtTrfTxInf');
        $xmlPmtInf->appendChild($xmlCdtTrfTxInf);

        $xmlPmtId = $this->instance->createElement('PmtId');
        $xmlCdtTrfTxInf->appendChild($xmlPmtId);

        $xmlEndToEndId = $this->instance->createElement('EndToEndId');
        if ($endToEndId) {
            $xmlEndToEndId->nodeValue = $endToEndId;
        } else {
            $xmlEndToEndId->nodeValue = $this->randomService->uniqueIdWithDate(
                'pete'.str_pad((string)$nbOfTxs, 2, '0')
            );
        }
        $xmlPmtId->appendChild($xmlEndToEndId);

        if (!is_null($instrId)) {
            $xmlInstrId = $this->instance->createElement('InstrId');
            $xmlInstrId->nodeValue = $instrId;
            $xmlPmtId->appendChild($xmlInstrId);
        }

        //update parent's elements
        $xmlNbOfTxs = DOMHelper::safeItem($nbOfTxsList);
        $xmlNbOfTxs->nodeValue = (string)$nbOfTxs;

        $nbOfTxsList = $xpath->query('//CstmrCdtTrfInitn/GrpHdr/NbOfTxs');
        $xmlNbOfTxs = DOMHelper::safeItem($nbOfTxsList);
        $xmlNbOfTxs->nodeValue = (string)$nbOfTxs;

        $ctrlSumList = $xpath->query('//CstmrCdtTrfInitn/GrpHdr/CtrlSum');
        $ctrlSum = (float)DOMHelper::safeItemValue($ctrlSumList);
        $xmlCtrlSum = DOMHelper::safeItem($ctrlSumList);
        $xmlCtrlSum->nodeValue = number_format($ctrlSum + $amount, 2, '.', '');

        return $xmlCdtTrfTxInf;
    }

    private function addAmountElement(DOMElement $xmlCdtTrfTxInf, float $amount, string $currency): void
    {
        $xmlAmt = $this->instance->createElement('Amt');
        $xmlCdtTrfTxInf->appendChild($xmlAmt);

        $xmlInstdAmt = $this->instance->createElement('InstdAmt');
        $xmlInstdAmt->setAttribute('Ccy', $currency);
        $xmlInstdAmt->nodeValue = number_format($amount, 2, '.', '');
        $xmlAmt->appendChild($xmlInstdAmt);
    }

    private function addCreditor(
        DOMElement $xmlCdtTrfTxInf,
        ?string $creditorFinInstBIC,
        string $creditorIBAN,
        string $creditorName,
        ?PostalAddressInterface $postalAddress,
        string $purpose = null
    ): void {
        //agent
        if ($creditorFinInstBIC !== null) {
            $xmlCdtrAgt = $this->instance->createElement('CdtrAgt');
            $xmlCdtTrfTxInf->appendChild($xmlCdtrAgt);

            $xmlFinInstnId = $this->instance->createElement('FinInstnId');
            $xmlCdtrAgt->appendChild($xmlFinInstnId);

            $xmlBIC = $this->instance->createElement('BIC');
            $xmlBIC->nodeValue = $creditorFinInstBIC;
            $xmlFinInstnId->appendChild($xmlBIC);
        }

        // Creditor
        $xmlCdtr = $this->instance->createElement('Cdtr');
        $xmlCdtTrfTxInf->appendChild($xmlCdtr);

        $xmlNm = $this->instance->createElement('Nm');
        $xmlNm->nodeValue = $creditorName;
        $xmlCdtr->appendChild($xmlNm);

        if ($postalAddress !== null) {
            $xmlCdtr->appendChild($postalAddress->toDomElement($this->instance));
        }

        // Account
        $xmlCdtrAcct = $this->instance->createElement('CdtrAcct');
        $xmlCdtTrfTxInf->appendChild($xmlCdtrAcct);

        $xmlId = $this->instance->createElement('Id');
        $xmlCdtrAcct->appendChild($xmlId);

        $xmlIBAN = $this->instance->createElement('IBAN');
        $xmlIBAN->nodeValue = str_replace(' ', '', $creditorIBAN);
        $xmlId->appendChild($xmlIBAN);

        // Purpose
        if ($purpose !== null) {
            $xmlRmtInf = $this->instance->createElement('RmtInf');
            $xmlCdtTrfTxInf->appendChild($xmlRmtInf);

            $xmlUstrd = $this->instance->createElement('Ustrd');
            $xmlUstrd->nodeValue = $purpose;
            $xmlRmtInf->appendChild($xmlUstrd);
        }
    }

    public function addBankTransaction(
        string $creditorIBAN,
        string $creditorName,
        ?PostalAddressInterface $postalAddress,
        float $amount,
        string $currency,
        string $purpose = null
    ): CustomerCreditTransferBuilder {
        if ($currency !== 'EUR') {
            throw new InvalidArgumentException('The SEPA transaction is restricted to EUR currency.');
        }

        $xmlCdtTrfTxInf = $this->createCreditTransferTransactionElement($amount);

        $this->addAmountElement($xmlCdtTrfTxInf, $amount, $currency);

        $this->addCreditor($xmlCdtTrfTxInf, null, $creditorIBAN, $creditorName, $postalAddress, $purpose);

        return $this;
    }

    public function addSEPATransaction(
        string $creditorFinInstBIC,
        string $creditorIBAN,
        string $creditorName,
        ?PostalAddressInterface $postalAddress,
        float $amount,
        string $currency,
        string $purpose = null
    ): CustomerCreditTransferBuilder {
        if ($currency !== 'EUR') {
            throw new InvalidArgumentException('The SEPA transaction is restricted to EUR currency.');
        }

        $xmlCdtTrfTxInf = $this->createCreditTransferTransactionElement($amount);

        // Payment Type Information
        $xmlPmtTpInf = $this->instance->createElement('PmtTpInf');
        $xmlCdtTrfTxInf->appendChild($xmlPmtTpInf);

        $xmlSvcLvl = $this->instance->createElement('SvcLvl');
        $xmlPmtTpInf->appendChild($xmlSvcLvl);

        $xmlCd = $this->instance->createElement('Cd');
        $xmlCd->nodeValue = 'SEPA';
        $xmlSvcLvl->appendChild($xmlCd);

        $this->addAmountElement($xmlCdtTrfTxInf, $amount, $currency);

        $xmlChrgBr = $this->instance->createElement('ChrgBr');
        $xmlChrgBr->nodeValue = 'SLEV';
        $xmlCdtTrfTxInf->appendChild($xmlChrgBr);

        $this->addCreditor(
            $xmlCdtTrfTxInf,
            $creditorFinInstBIC,
            $creditorIBAN,
            $creditorName,
            $postalAddress,
            $purpose
        );

        return $this;
    }

    public function addForeignTransaction(
        string $creditorFinInstBIC,
        string $creditorIBAN,
        string $creditorName,
        ?PostalAddressInterface $postalAddress,
        float $amount,
        string $currency,
        string $purpose = null
    ): CustomerCreditTransferBuilder {
        $xmlCdtTrfTxInf = $this->createCreditTransferTransactionElement($amount);

        $this->addAmountElement($xmlCdtTrfTxInf, $amount, $currency);

        $this->addCreditor(
            $xmlCdtTrfTxInf,
            $creditorFinInstBIC,
            $creditorIBAN,
            $creditorName,
            $postalAddress,
            $purpose
        );

        return $this;
    }

    public function popInstance(): CustomerCreditTransfer
    {
        $instance = $this->instance;
        $this->instance = null;

        return $instance;
    }
}
