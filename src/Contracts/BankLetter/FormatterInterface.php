<?php

namespace EbicsApi\Ebics\Contracts\BankLetter;

use EbicsApi\Ebics\Models\BankLetter;

/**
 * EBICS formatter for bank letter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface FormatterInterface
{

    /**
     * Format bank letter to printable.
     *
     * @param BankLetter $bankLetter
     *
     * @return string
     */
    public function format(BankLetter $bankLetter): string;

    /**
     * Set translations.
     *
     * @param array $translations
     *
     * @return void
     */
    public function setTranslations(array $translations): void;
}
