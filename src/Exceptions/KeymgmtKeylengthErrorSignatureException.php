<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * KeymgmtKeylengthErrorSignatureException used for 091204 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class KeymgmtKeylengthErrorSignatureException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091204',
            $responseMessage,
            'When processing an INI request, the order data contains ' .
            'an bank-technical key of inadmissible length.'
        );
    }
}
