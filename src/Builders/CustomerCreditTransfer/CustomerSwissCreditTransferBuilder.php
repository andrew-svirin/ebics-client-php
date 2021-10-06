<?php

namespace AndrewSvirin\Ebics\Builders\CustomerCreditTransfer;

use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\CustomerCreditTransfer;
use AndrewSvirin\Ebics\Models\PostalAddressInterface;
use AndrewSvirin\Ebics\Services\DOMHelper;
use AndrewSvirin\Ebics\Services\RandomService;
use DateTime;

/**
 * Class CustomerSwissCreditTransferBuilder builder for model @see \AndrewSvirin\Ebics\Models\CustomerCreditTransfer
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Jonathan Luthi
 */
class CustomerSwissCreditTransferBuilder
{

    use XPathTrait;

    /**
     * @var RandomService
     */
    private $randomService;

    /**
     * @var CustomerCreditTransfer|null
     */
    private $instance;

    public function __construct()
    {
        $this->randomService = new RandomService();
    }

    public function createInstance(
        string $debitorFinInstBIC,
        string $debitorIBAN,
        string $debitorName
    ): CustomerSwissCreditTransferBuilder {
        $this->instance = new CustomerCreditTransfer();
        $now = new DateTime();

        $xmDocument = $this->instance->createElementNS(
            'http://www.six-interbank-clearing.com/de/pain.001.001.03.ch.02.xsd',
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
            'http://www.six-interbank-clearing.com/de/pain.001.001.03.ch.02.xsd pain.001.001.03.ch.02.xsd'
        );
        $this->instance->appendChild($xmDocument);

        $xmlCstmrCdtTrfInitn = $this->instance->createElement('CstmrCdtTrfInitn');
        $xmDocument->appendChild($xmlCstmrCdtTrfInitn);

        //header
        $xmlGrpHdr = $this->instance->createElement('GrpHdr');
        $xmlCstmrCdtTrfInitn->appendChild($xmlGrpHdr);

        $xmlMsgId = $this->instance->createElement('MsgId');
        $xmlMsgId->nodeValue = substr($this->randomService->uniqueIdWithDate('msg'), 0, 35); // example: MSGID-9214-170502115114-00
        $xmlGrpHdr->appendChild($xmlMsgId);

        $xmlMsgId = $this->instance->createElement('CreDtTm');
        $xmlMsgId->nodeValue = $now->format('Y-m-d\TH:i:s');
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

        $xmlCtctDtls = $this->instance->createElement('CtctDtls');
        $xmlInitgPty->appendChild($xmlCtctDtls);

        $xmlNm = $this->instance->createElement('Nm');
        $xmlNm->nodeValue = 'Ebics client PHP';
        $xmlCtctDtls->appendChild($xmlNm);

        $xmlOthr = $this->instance->createElement('Othr');
        $xmlOthr->nodeValue = '1.9';
        $xmlCtctDtls->appendChild($xmlOthr);

        //payment information
        $xmlPmtInf = $this->instance->createElement('PmtInf');
        $xmlCstmrCdtTrfInitn->appendChild($xmlPmtInf);

        $xmlPmtInfId = $this->instance->createElement('PmtInfId');
        $xmlPmtInfId->nodeValue = substr($this->randomService->uniqueIdWithDate('pmt'), 0, 35); // example PmtInfId-BP01-POS-01
        $xmlPmtInf->appendChild($xmlPmtInfId);

        $xmlPmtMtd = $this->instance->createElement('PmtMtd');
        $xmlPmtMtd->nodeValue = 'TRF';
        $xmlPmtInf->appendChild($xmlPmtMtd);

        /*$xmlBtchBookg = $this->instance->createElement('BtchBookg');
        $xmlBtchBookg->nodeValue = 'true';
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
        $xmlSvcLvl->appendChild($xmlCd);*/

        $xmlReqdExctnDt = $this->instance->createElement('ReqdExctnDt');
        $xmlReqdExctnDt->nodeValue = $now->format('Y-m-d');
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

        return $this;
    }

    public function addBankTransaction(
        string $creditorFinInstBIC,
        string $creditorIBAN,
        string $creditorName,
        ?PostalAddressInterface $postalAddress,
        float $amount,
        string $currency,
        string $purpose = null
    ): CustomerSwissCreditTransferBuilder {
        if (!in_array($currency, ['CHF', 'EUR'], true)) {
            //throw new InvalidArgumentException('The SEPA transaction is restricted to CHF and EUR currency.');
            return $this;
        }

        $xpath = $this->prepareXPath($this->instance);
        $nbOfTxsList = $xpath->query(/*'//CstmrCdtTrfInitn/PmtInf/NbOfTxs'*/'//CstmrCdtTrfInitn//GrpHdr/NbOfTxs');
        $nbOfTxs = (int)DOMHelper::safeItemValue($nbOfTxsList);
        $nbOfTxs++;

        $pmtInfList = $xpath->query('//CstmrCdtTrfInitn/PmtInf');
        $xmlPmtInf = DOMHelper::safeItem($pmtInfList);

        $xmlCdtTrfTxInf = $this->instance->createElement('CdtTrfTxInf');
        $xmlPmtInf->appendChild($xmlCdtTrfTxInf);

        $xmlPmtId = $this->instance->createElement('PmtId');
        $xmlCdtTrfTxInf->appendChild($xmlPmtId);

        $xmlInstrId = $this->instance->createElement('InstrId');
        $xmlInstrId->nodeValue = substr($this->randomService->uniqueIdWithDate('pii' . str_pad((string)$nbOfTxs, 2, '0')), 0, 35);
        $xmlPmtId->appendChild($xmlInstrId);

        $xmlEndToEndId = $this->instance->createElement('EndToEndId');
        $xmlEndToEndId->nodeValue = substr($this->randomService->uniqueIdWithDate('pete' . str_pad((string)$nbOfTxs, 2, '0')), 0, 35);
        $xmlPmtId->appendChild($xmlEndToEndId);

        $xmlAmt = $this->instance->createElement('Amt');
        $xmlCdtTrfTxInf->appendChild($xmlAmt);

        $xmlInstdAmt = $this->instance->createElement('InstdAmt');
        $xmlInstdAmt->setAttribute('Ccy', $currency);
        $xmlInstdAmt->nodeValue = number_format($amount, 2, '.', '');
        $xmlAmt->appendChild($xmlInstdAmt);

        $xmlCdtrAgt = $this->instance->createElement('CdtrAgt');
        $xmlCdtTrfTxInf->appendChild($xmlCdtrAgt);

        $xmlFinInstnId = $this->instance->createElement('FinInstnId');
        $xmlCdtrAgt->appendChild($xmlFinInstnId);

        $xmlBIC = $this->instance->createElement('BIC');
        $xmlBIC->nodeValue = $creditorFinInstBIC;
        $xmlFinInstnId->appendChild($xmlBIC);

        $xmlCdtr = $this->instance->createElement('Cdtr');
        $xmlCdtTrfTxInf->appendChild($xmlCdtr);

        $xmlNm = $this->instance->createElement('Nm');
        $xmlNm->nodeValue = $creditorName;
        $xmlCdtr->appendChild($xmlNm);

        if ($postalAddress !== null) {
            $xmlCdtr->appendChild($postalAddress->toDomElement($this->instance));
        }

        $xmlCdtrAcct = $this->instance->createElement('CdtrAcct');
        $xmlCdtTrfTxInf->appendChild($xmlCdtrAcct);

        $xmlId = $this->instance->createElement('Id');
        $xmlCdtrAcct->appendChild($xmlId);

        $xmlIBAN = $this->instance->createElement('IBAN');
        $xmlIBAN->nodeValue = $creditorIBAN;
        $xmlId->appendChild($xmlIBAN);

        if ($purpose !== null && trim($purpose) !== '') {
            $xmlRmtInf = $this->instance->createElement('RmtInf');
            $xmlCdtTrfTxInf->appendChild($xmlRmtInf);

            $xmlUstrd = $this->instance->createElement('Ustrd');
            $xmlUstrd->nodeValue = $purpose;
            $xmlRmtInf->appendChild($xmlUstrd);
        }

        $xmlNbOfTxs = DOMHelper::safeItem($nbOfTxsList);
        $xmlNbOfTxs->nodeValue = (string)$nbOfTxs;

        $nbOfTxsList = $xpath->query('//CstmrCdtTrfInitn/GrpHdr/NbOfTxs');
        $xmlNbOfTxs = DOMHelper::safeItem($nbOfTxsList);
        $xmlNbOfTxs->nodeValue = (string)$nbOfTxs;

        $ctrlSumList = $xpath->query('//CstmrCdtTrfInitn/GrpHdr/CtrlSum');
        $ctrlSum = (float)DOMHelper::safeItemValue($ctrlSumList);
        $xmlCtrlSum = DOMHelper::safeItem($ctrlSumList);
        $xmlCtrlSum->nodeValue = number_format($ctrlSum + $amount, 2, '.', '');

        $ctrlSumList = $xpath->query('//CstmrCdtTrfInitn/PmtInf/CtrlSum');
        $ctrlSum = (float)DOMHelper::safeItemValue($ctrlSumList);
        $xmlCtrlSum = DOMHelper::safeItem($ctrlSumList);
        $xmlCtrlSum->nodeValue = number_format($ctrlSum + $amount, 2, '.', '');

        return $this;
    }

    //todo: public function addIS1Transaction
    //todo: public function addIS2Transaction

    public function addSEPATransaction(
        string $creditorFinInstBIC,
        string $creditorIBAN,
        string $creditorName,
        ?PostalAddressInterface $postalAddress,
        float $amount,
        string $currency,
        string $purpose = null
    ): CustomerSwissCreditTransferBuilder {
        if ($currency !== 'EUR') {
            //throw new InvalidArgumentException('The SEPA transaction is restricted to EUR currency.');
            return $this;
        }

        $xpath = $this->prepareXPath($this->instance);
        $nbOfTxsList = $xpath->query(/*'//CstmrCdtTrfInitn/PmtInf/NbOfTxs'*/'//CstmrCdtTrfInitn//GrpHdr/NbOfTxs');
        $nbOfTxs = (int)DOMHelper::safeItemValue($nbOfTxsList);
        $nbOfTxs++;

        $pmtInfList = $xpath->query('//CstmrCdtTrfInitn/PmtInf');
        $xmlPmtInf = DOMHelper::safeItem($pmtInfList);

        $xmlCdtTrfTxInf = $this->instance->createElement('CdtTrfTxInf');
        $xmlPmtInf->appendChild($xmlCdtTrfTxInf);

        $xmlPmtId = $this->instance->createElement('PmtId');
        $xmlCdtTrfTxInf->appendChild($xmlPmtId);

        $xmlInstrId = $this->instance->createElement('InstrId');
        $xmlInstrId->nodeValue = substr($this->randomService->uniqueIdWithDate('pii' . str_pad((string)$nbOfTxs, 2, '0')), 0, 35);
        $xmlPmtId->appendChild($xmlInstrId);

        $xmlEndToEndId = $this->instance->createElement('EndToEndId');
        $xmlEndToEndId->nodeValue = substr($this->randomService->uniqueIdWithDate('pete' . str_pad((string)$nbOfTxs, 2, '0')), 0, 35);
        $xmlPmtId->appendChild($xmlEndToEndId);

        $xmlPmtTpInf = $this->instance->createElement('PmtTpInf');
        $xmlCdtTrfTxInf->appendChild($xmlPmtTpInf);

        $xmlSvcLvl = $this->instance->createElement('SvcLvl');
        $xmlPmtTpInf->appendChild($xmlSvcLvl);

        $xmlCd = $this->instance->createElement('Cd');
        $xmlCd->nodeValue = 'SEPA';
        $xmlSvcLvl->appendChild($xmlCd);

        $xmlAmt = $this->instance->createElement('Amt');
        $xmlCdtTrfTxInf->appendChild($xmlAmt);

        $xmlInstdAmt = $this->instance->createElement('InstdAmt');
        $xmlInstdAmt->setAttribute('Ccy', $currency);
        $xmlInstdAmt->nodeValue = number_format($amount, 2, '.', '');
        $xmlAmt->appendChild($xmlInstdAmt);

        $xmlChrgBr = $this->instance->createElement('ChrgBr');
        $xmlChrgBr->nodeValue = 'SLEV';
        $xmlCdtTrfTxInf->appendChild($xmlChrgBr);

        $xmlCdtrAgt = $this->instance->createElement('CdtrAgt');
        $xmlCdtTrfTxInf->appendChild($xmlCdtrAgt);

        $xmlFinInstnId = $this->instance->createElement('FinInstnId');
        $xmlCdtrAgt->appendChild($xmlFinInstnId);

        $xmlBIC = $this->instance->createElement('BIC');
        $xmlBIC->nodeValue = $creditorFinInstBIC;
        $xmlFinInstnId->appendChild($xmlBIC);

        $xmlCdtr = $this->instance->createElement('Cdtr');
        $xmlCdtTrfTxInf->appendChild($xmlCdtr);

        $xmlNm = $this->instance->createElement('Nm');
        $xmlNm->nodeValue = $creditorName;
        $xmlCdtr->appendChild($xmlNm);

        if ($postalAddress !== null) {
            $xmlCdtr->appendChild($postalAddress->toDomElement($this->instance));
        }

        $xmlCdtrAcct = $this->instance->createElement('CdtrAcct');
        $xmlCdtTrfTxInf->appendChild($xmlCdtrAcct);

        $xmlId = $this->instance->createElement('Id');
        $xmlCdtrAcct->appendChild($xmlId);

        $xmlIBAN = $this->instance->createElement('IBAN');
        $xmlIBAN->nodeValue = str_replace(' ', '', $creditorIBAN);
        $xmlId->appendChild($xmlIBAN);

        if ($purpose !== null && trim($purpose) !== '') {
            $xmlRmtInf = $this->instance->createElement('RmtInf');
            $xmlCdtTrfTxInf->appendChild($xmlRmtInf);

            $xmlUstrd = $this->instance->createElement('Ustrd');
            $xmlUstrd->nodeValue = $purpose;
            $xmlRmtInf->appendChild($xmlUstrd);
        }

        $xmlNbOfTxs = DOMHelper::safeItem($nbOfTxsList);
        $xmlNbOfTxs->nodeValue = (string)$nbOfTxs;

        $nbOfTxsList = $xpath->query('//CstmrCdtTrfInitn/GrpHdr/NbOfTxs');
        $xmlNbOfTxs = DOMHelper::safeItem($nbOfTxsList);
        $xmlNbOfTxs->nodeValue = (string)$nbOfTxs;

        $ctrlSumList = $xpath->query('//CstmrCdtTrfInitn/GrpHdr/CtrlSum');
        $ctrlSum = (float)DOMHelper::safeItemValue($ctrlSumList);
        $xmlCtrlSum = DOMHelper::safeItem($ctrlSumList);
        $xmlCtrlSum->nodeValue = number_format($ctrlSum + $amount, 2, '.', '');

        $ctrlSumList = $xpath->query('//CstmrCdtTrfInitn/PmtInf/CtrlSum');
        $ctrlSum = (float)DOMHelper::safeItemValue($ctrlSumList);
        $xmlCtrlSum = DOMHelper::safeItem($ctrlSumList);
        $xmlCtrlSum->nodeValue = number_format($ctrlSum + $amount, 2, '.', '');

        return $this;
    }

    //todo: public function addForeignTransaction
    //todo: public function addISRTransaction

    public function popInstance(): CustomerCreditTransfer
    {
        $instance = $this->instance;
        $this->instance = null;

        return $instance;
    }
}
