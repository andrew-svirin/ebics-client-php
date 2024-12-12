<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * TxMessageReplayException used for 091103 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class TxMessageReplayException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091103',
            $responseMessage,
            'To avoid replay, the bank compares the received Nonce with the list of nonce ' .
            'values that were received previously and stored locally. If the nonce received is ' .
            'greater than the tolerance period specified by the bank, ' .
            'the response EBICS_TX_MESSAGE_REPLAY is returned.'
        );
    }
}
