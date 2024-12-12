<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * InvalidUserStateException used for 091004 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class InvalidUserStateException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091004',
            $responseMessage,
            'The identification and authentication signature of the technical user ' .
            'is successfully verified and the non-technical subscriber is known to the bank, ' .
            'but the user is not in a ’Ready’ state.'
        );
    }
}
