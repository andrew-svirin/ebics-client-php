<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\BankLetter;

/**
 * EBICS BankLetterFormatterInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface BankLetterFormatterInterface
{

    /**
     * Format bank letter to printable.
     *
     * @param BankLetter $bankLetter
     *
     * @return mixed
     */
    public function format(BankLetter $bankLetter);
}
