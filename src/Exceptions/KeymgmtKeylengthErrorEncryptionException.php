<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * KeymgmtKeylengthErrorEncryptionException used for 091206 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class KeymgmtKeylengthErrorEncryptionException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091206',
            $responseMessage,
            'When processing an HIA request, the order data contains an ' .
            'encryption key of inadmissible length.'
        );
    }
}
