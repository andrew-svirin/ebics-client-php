<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * SignerUnknownException used for 091304 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class SignerUnknownException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091304',
            $responseMessage,
            'The signatory of the order is not a valid subscriber.'
        );
    }
}
