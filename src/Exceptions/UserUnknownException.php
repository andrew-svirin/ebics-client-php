<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * UserUnknownException used for 091003 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class UserUnknownException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091003',
            $responseMessage,
            'The identification and authentication signature of the technical user is ' .
            'successfully verified but the non-technical subscriber is not known to the bank.'
        );
    }
}
