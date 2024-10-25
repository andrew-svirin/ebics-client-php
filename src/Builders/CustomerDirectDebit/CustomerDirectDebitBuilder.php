<?php

namespace AndrewSvirin\Ebics\Builders\CustomerDirectDebit;

use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\CustomerDirectDebit;
use AndrewSvirin\Ebics\Services\DOMHelper;
use AndrewSvirin\Ebics\Services\RandomService;
use DateTime;

/**
 * Class CustomerDirectDebitBuilder builder for model @see \AndrewSvirin\Ebics\Models\CustomerDirectDebit
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class CustomerDirectDebitBuilder
{
    use XPathTrait;

    private RandomService $randomService;
    private ?CustomerDirectDebit $instance;

    public function __construct()
    {
        $this->randomService = new RandomService();
    }

    /**
     * @param string $schema namespace schema urn:iso:std:iso:20022:tech:xsd:pain.008.001.02
     * @param string $creditorFinInstBic
     * @param string $creditorIban
     * @param string $creditorName
     * @param string|null $creditorId
     * @param string $sequenceType FRST | RCUR
     * @param DateTime|null $collectionDate
     * @param bool $batchBooking By deactivating the batch booking procedure,
     * you request your credit institution to book each transaction within this order separately.
     * @param string|null $msgId Overwrite default generated message id - should be unique at
     * least for 15 days. Used for rejecting duplicated transactions (max length: 35 characters)
     * @param string|null $paymentReference Overwrite default payment reference -
     * visible on creditors bank statement (max length: 35 characters)
     * @param string $localInstrument Define whether the payment in question is Core or B2B: (CORE | B2B)
     * @return $this
     * @throws \DOMException
     */
    public function createInstance(
        string $schema,
        string $creditorFinInstBic,
        string $creditorIban,
        string $creditorName,
        string $creditorId = null,
        string $sequenceType = 'FRST',
        DateTime $collectionDate = null,
        bool $batchBooking = true,
        string $msgId = null,
        string $paymentReference = null,
        string $localInstrument = 'CORE'
    ): CustomerDirectDebitBuilder {
        $this->instance = new CustomerDirectDebit();
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
        $this->instance->appendChild($xmDocument);

        $xmlCstmrDrctDbtInitn = $this->instance->createElement('CstmrDrctDbtInitn');
        $xmDocument->appendChild($xmlCstmrDrctDbtInitn);

        $xmlGrpHdr = $this->instance->createElement('GrpHdr');
        $xmlCstmrDrctDbtInitn->appendChild($xmlGrpHdr);

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
        $xmlNm->nodeValue = $creditorName;
        $xmlInitgPty->appendChild($xmlNm);

        $xmlPmtInf = $this->instance->createElement('PmtInf');
        $xmlCstmrDrctDbtInitn->appendChild($xmlPmtInf);

        $xmlPmtInfId = $this->instance->createElement('PmtInfId');
        if ($paymentReference) {
            $xmlPmtInfId->nodeValue = $paymentReference;
        } else {
            $xmlPmtInfId->nodeValue = $this->randomService->uniqueIdWithDate('pmt');
        }
        $xmlPmtInf->appendChild($xmlPmtInfId);

        $xmlPmtMtd = $this->instance->createElement('PmtMtd');
        $xmlPmtMtd->nodeValue = 'DD';
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

        $xmlLclInstrm = $this->instance->createElement('LclInstrm');
        $xmlPmtTpInf->appendChild($xmlLclInstrm);

        $xmlCd = $this->instance->createElement('Cd');
        $xmlCd->nodeValue = $localInstrument;
        $xmlLclInstrm->appendChild($xmlCd);

        $xmlSeqTp = $this->instance->createElement('SeqTp');
        $xmlSeqTp->nodeValue = $sequenceType;
        $xmlPmtTpInf->appendChild($xmlSeqTp);

        $xmlReqdColltnDt = $this->instance->createElement('ReqdColltnDt');
        if ($collectionDate) {
            $xmlReqdColltnDt->nodeValue = $collectionDate->format('Y-m-d');
        } else {
            $xmlReqdColltnDt->nodeValue = $now->format('Y-m-d');
        }
        $xmlPmtInf->appendChild($xmlReqdColltnDt);

        $xmlCdtr = $this->instance->createElement('Cdtr');
        $xmlPmtInf->appendChild($xmlCdtr);

        $xmlNm = $this->instance->createElement('Nm');
        $xmlNm->nodeValue = $creditorName;
        $xmlCdtr->appendChild($xmlNm);

        $xmlCdtrAcct = $this->instance->createElement('CdtrAcct');
        $xmlPmtInf->appendChild($xmlCdtrAcct);

        $xmlId = $this->instance->createElement('Id');
        $xmlCdtrAcct->appendChild($xmlId);

        $xmlIBAN = $this->instance->createElement('IBAN');
        $xmlIBAN->nodeValue = $creditorIban;
        $xmlId->appendChild($xmlIBAN);

        $xmlCdtrAgt = $this->instance->createElement('CdtrAgt');
        $xmlPmtInf->appendChild($xmlCdtrAgt);

        $xmlFinInstnId = $this->instance->createElement('FinInstnId');
        $xmlCdtrAgt->appendChild($xmlFinInstnId);

        $xmlBIC = $this->instance->createElement('BIC');
        $xmlBIC->nodeValue = $creditorFinInstBic;
        $xmlFinInstnId->appendChild($xmlBIC);

        $xmlChrgBr = $this->instance->createElement('ChrgBr');
        $xmlChrgBr->nodeValue = 'SLEV';
        $xmlPmtInf->appendChild($xmlChrgBr);

        if ($creditorId) {
            $xmlCdtrSchmeId = $this->instance->createElement('CdtrSchmeId');
            $xmlPmtInf->appendChild($xmlCdtrSchmeId);

            $xmlCdtrSchmeIdId = $this->instance->createElement('Id');
            $xmlCdtrSchmeId->appendChild($xmlCdtrSchmeIdId);

            $xmlCdtrSchmePrvtId = $this->instance->createElement('PrvtId');
            $xmlCdtrSchmeIdId->appendChild($xmlCdtrSchmePrvtId);

            $xmlCdtrSchmeIdIdPrvtIdOthr = $this->instance->createElement('Othr');
            $xmlCdtrSchmePrvtId->appendChild($xmlCdtrSchmeIdIdPrvtIdOthr);

            $xmlCdtrSchmeOthrId = $this->instance->createElement('Id');
            $xmlCdtrSchmeOthrId->nodeValue = $creditorId;
            $xmlCdtrSchmeIdIdPrvtIdOthr->appendChild($xmlCdtrSchmeOthrId);

            $xmlCdtrSchmeSchmeNm = $this->instance->createElement('SchmeNm');
            $xmlCdtrSchmeIdIdPrvtIdOthr->appendChild($xmlCdtrSchmeSchmeNm);

            $xmlCdtrSchmePrtry = $this->instance->createElement('Prtry');
            $xmlCdtrSchmePrtry->nodeValue = 'SEPA';
            $xmlCdtrSchmeSchmeNm->appendChild($xmlCdtrSchmePrtry);
        }

        return $this;
    }

    public function addTransaction(
        string $debitorFinInstBIC,
        string $debitorIBAN,
        string $debitorName,
        float $amount,
        string $currency,
        string $purpose,
        string $mandateReference = '1',
        DateTime $signatureDate = null,
        string $endToEndId = null
    ): CustomerDirectDebitBuilder {
        $xpath = $this->prepareXPath($this->instance);
        $nbOfTxsList = $xpath->query('//CstmrDrctDbtInitn/PmtInf/NbOfTxs');
        $nbOfTxs = (int)DOMHelper::safeItemValue($nbOfTxsList);
        $nbOfTxs++;

        $pmtInfList = $xpath->query('//CstmrDrctDbtInitn/PmtInf');
        $xmlPmtInf = DOMHelper::safeItem($pmtInfList);

        $reqdColltnDtList = $xpath->query('//CstmrDrctDbtInitn/PmtInf/ReqdColltnDt');
        $reqdColltnDt = DOMHelper::safeItemValue($reqdColltnDtList);

        $xmlDrctDbtTxInf = $this->instance->createElement('DrctDbtTxInf');
        $xmlPmtInf->appendChild($xmlDrctDbtTxInf);

        $xmlPmtId = $this->instance->createElement('PmtId');
        $xmlDrctDbtTxInf->appendChild($xmlPmtId);

        $xmlEndToEndId = $this->instance->createElement('EndToEndId');
        if ($endToEndId) {
            $xmlEndToEndId->nodeValue = $endToEndId;
        } else {
            $xmlEndToEndId->nodeValue = $this->randomService->uniqueIdWithDate(
                'pete'.str_pad((string)$nbOfTxs, 2, '0')
            );
        }

        $xmlPmtId->appendChild($xmlEndToEndId);

        $xmlInstdAmt = $this->instance->createElement('InstdAmt');
        $xmlInstdAmt->setAttribute('Ccy', $currency);
        $xmlInstdAmt->nodeValue = number_format($amount, 2, '.', '');
        $xmlDrctDbtTxInf->appendChild($xmlInstdAmt);

        $xmlDrctDbtTx = $this->instance->createElement('DrctDbtTx');
        $xmlDrctDbtTxInf->appendChild($xmlDrctDbtTx);

        $xmlMndtRltdInf = $this->instance->createElement('MndtRltdInf');
        $xmlDrctDbtTx->appendChild($xmlMndtRltdInf);

        $xmlMndtId = $this->instance->createElement('MndtId');
        $xmlMndtId->nodeValue = $mandateReference;
        $xmlMndtRltdInf->appendChild($xmlMndtId);

        $xmlDtOfSgntr = $this->instance->createElement('DtOfSgntr');
        if ($signatureDate) {
            $xmlDtOfSgntr->nodeValue = $signatureDate->format('Y-m-d');
        } else {
            $xmlDtOfSgntr->nodeValue = $reqdColltnDt;
        }
        $xmlMndtRltdInf->appendChild($xmlDtOfSgntr);

        $xmlDbtrAgt = $this->instance->createElement('DbtrAgt');
        $xmlDrctDbtTxInf->appendChild($xmlDbtrAgt);

        $xmlFinInstnId = $this->instance->createElement('FinInstnId');
        $xmlDbtrAgt->appendChild($xmlFinInstnId);

        $xmlBIC = $this->instance->createElement('BIC');
        $xmlBIC->nodeValue = $debitorFinInstBIC;
        $xmlFinInstnId->appendChild($xmlBIC);

        $xmlDbtr = $this->instance->createElement('Dbtr');
        $xmlDrctDbtTxInf->appendChild($xmlDbtr);

        $xmlNm = $this->instance->createElement('Nm');
        $xmlNm->nodeValue = $debitorName;
        $xmlDbtr->appendChild($xmlNm);

        $xmlDbtrAcct = $this->instance->createElement('DbtrAcct');
        $xmlDrctDbtTxInf->appendChild($xmlDbtrAcct);

        $xmlId = $this->instance->createElement('Id');
        $xmlDbtrAcct->appendChild($xmlId);

        $xmlIBAN = $this->instance->createElement('IBAN');
        $xmlIBAN->nodeValue = $debitorIBAN;
        $xmlId->appendChild($xmlIBAN);

        $xmlRmtInf = $this->instance->createElement('RmtInf');
        $xmlDrctDbtTxInf->appendChild($xmlRmtInf);

        $xmlUstrd = $this->instance->createElement('Ustrd');
        $xmlUstrd->nodeValue = $purpose;
        $xmlRmtInf->appendChild($xmlUstrd);

        $xmlNbOfTxs = DOMHelper::safeItem($nbOfTxsList);
        $xmlNbOfTxs->nodeValue = (string)$nbOfTxs;

        $nbOfTxsList = $xpath->query('//CstmrDrctDbtInitn/GrpHdr/NbOfTxs');
        $xmlNbOfTxs = DOMHelper::safeItem($nbOfTxsList);
        $xmlNbOfTxs->nodeValue = (string)$nbOfTxs;

        $ctrlSumList = $xpath->query('//CstmrDrctDbtInitn/GrpHdr/CtrlSum');
        $ctrlSum = (float)DOMHelper::safeItemValue($ctrlSumList);
        $xmlCtrlSum = DOMHelper::safeItem($ctrlSumList);
        $xmlCtrlSum->nodeValue = number_format($ctrlSum + $amount, 2, '.', '');

        $ctrlSumList = $xpath->query('//CstmrDrctDbtInitn/PmtInf/CtrlSum');
        $ctrlSum = (float)DOMHelper::safeItemValue($ctrlSumList);
        $xmlCtrlSum = DOMHelper::safeItem($ctrlSumList);
        $xmlCtrlSum->nodeValue = number_format($ctrlSum + $amount, 2, '.', '');

        return $this;
    }

    public function popInstance(): CustomerDirectDebit
    {
        $instance = $this->instance;
        $this->instance = null;

        return $instance;
    }
}
