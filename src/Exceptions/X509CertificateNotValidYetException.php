<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * X509CertificateNotValidYetException used for 091209 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class X509CertificateNotValidYetException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091209',
            $responseMessage,
            'The certificate is not valid because it is not yet in effect.'
        );
    }
}
