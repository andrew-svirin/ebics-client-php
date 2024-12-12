<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * X509WrongKeyUsageException used for 091210 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class X509WrongKeyUsageException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091210',
            $responseMessage,
            'When verifying the certificate key usage, the bank ' .
            'detects that the certificate is not issued for current use.'
        );
    }
}
