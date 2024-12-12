<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * KeymgmtDuplicateKeyException used for 091218 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class KeymgmtDuplicateKeyException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091218',
            $responseMessage,
            'The key sent for authentication or encryption is the same as the signature key.'
        );
    }
}
