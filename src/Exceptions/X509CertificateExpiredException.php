<?php

namespace AndrewSvirin\Ebics\Exceptions;

/**
 * X509CertificateExpiredException used for 091208 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class X509CertificateExpiredException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091208',
            $responseMessage,
            'The certificate is not valid because it has expired.'
        );
    }
}
