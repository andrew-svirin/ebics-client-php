<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * X509CtlInvalidException used for 091213 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class X509CtlInvalidException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091213',
            $responseMessage,
            'When verifying the certificate, the bank detects ' .
            'that the certificate trust list (CTL) is not valid.'
        );
    }
}
