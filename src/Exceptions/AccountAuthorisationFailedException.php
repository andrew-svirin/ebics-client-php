<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * AccountAuthorisationFailedException used for 091302 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class AccountAuthorisationFailedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091302',
            $responseMessage,
            'Preliminary verification of the account authorization has failed.'
        );
    }
}
