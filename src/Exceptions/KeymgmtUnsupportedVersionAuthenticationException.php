<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * KeymgmtUnsupportedVersionAuthenticationException used for 091202 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class KeymgmtUnsupportedVersionAuthenticationException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091202',
            $responseMessage,
            'When processing an HIA request, the order data contains an inadmissible ' .
            'version of the identification and authentication signature process.'
        );
    }
}
