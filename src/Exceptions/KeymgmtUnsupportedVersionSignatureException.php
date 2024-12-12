<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * KeymgmtUnsupportedVersionSignatureException used for 091201 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class KeymgmtUnsupportedVersionSignatureException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091201',
            $responseMessage,
            'When processing an INI request, the order data contains an inadmissible ' .
            'version of the bank-technical signature process.'
        );
    }
}
