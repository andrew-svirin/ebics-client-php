<?php

namespace AndrewSvirin\Ebics\Contracts;

/**
 * EBICS TransactionInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface TransactionInterface
{
    const PHASE_INITIALIZATION = 'Initialisation';
    const PHASE_RECEIPT = 'Receipt';
    const PHASE_TRANSFER = 'Transfer';

    //The value of the acknowledgement is 0 (“positive acknowledgement”) if download and processing of the order data was successful
    const CODE_RECEIPT_POSITIVE = 0;

    // Otherwise the value of the acknowledgement is 1 (“negative acknowledgement”).
    const CODE_RECEIPT_NEGATIVE = 1;
}
