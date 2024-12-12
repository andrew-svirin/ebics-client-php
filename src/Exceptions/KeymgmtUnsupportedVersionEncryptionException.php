<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * KeymgmtUnsupportedVersionEncryptionException used for 091203 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class KeymgmtUnsupportedVersionEncryptionException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091203',
            $responseMessage,
            'When processing an HIA request, the order data contains an inadmissible ' .
            'version of the encryption process.'
        );
    }
}
