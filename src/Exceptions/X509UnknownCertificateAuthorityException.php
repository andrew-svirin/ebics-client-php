<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * X509UnknownCertificateAuthorityException used for 091214 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class X509UnknownCertificateAuthorityException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091214',
            $responseMessage,
            'The chain cannot be verified because of an unknown certificate authority (CA).'
        );
    }
}
