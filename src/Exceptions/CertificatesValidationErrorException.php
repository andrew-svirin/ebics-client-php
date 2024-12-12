<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * CertificatesValidationErrorException used for 091219 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class CertificatesValidationErrorException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091219',
            $responseMessage,
            'The server is unable to match the certificate with the ' .
            'previously declared information automatically.'
        );
    }
}
