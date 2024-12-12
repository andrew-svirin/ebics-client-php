<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * KeymgmtNoX509SupportException used for 091207 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class KeymgmtNoX509SupportException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091207',
            $responseMessage,
            'A public key of type X509 is sent to the bank but the bank ' .
            'supports only public key value type.'
        );
    }
}
