<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * KeymgmtKeylengthErrorAuthenticationException used for 091205 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class KeymgmtKeylengthErrorAuthenticationException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091205',
            $responseMessage,
            'When processing an HIA request, the order data contains an identification ' .
            'and authentication key of inadmissible length.'
        );
    }
}
