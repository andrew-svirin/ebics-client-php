<?php

namespace AndrewSvirin\Ebics\contracts;

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

}