<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * X509WrongAlgorithmException used for 091211 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class X509WrongAlgorithmException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091211',
            $responseMessage,
            'When verifying the certificate algorithm, the bank ' .
            'detects that the certificate is not issued for current use.'
        );
    }
}
