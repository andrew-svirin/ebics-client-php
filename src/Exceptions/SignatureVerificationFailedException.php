<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * SignatureVerificationFailedException used for 091301 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class SignatureVerificationFailedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091301',
            $responseMessage,
            'Verification of the electronic signature has failed.'
        );
    }
}
