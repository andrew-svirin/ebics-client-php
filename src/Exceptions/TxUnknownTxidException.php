<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * TxUnknownTxidException used for 091101 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class TxUnknownTxidException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091101',
            $responseMessage,
            'The supplied transaction ID is invalid.'
        );
    }
}
