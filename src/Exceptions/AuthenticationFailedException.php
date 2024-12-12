<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * AuthenticationFailedException used for 061001 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class AuthenticationFailedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '061001',
            $responseMessage,
            'The bank is unable to verify the identification and authentication signature of an EBICS request.'
        );
    }
}
