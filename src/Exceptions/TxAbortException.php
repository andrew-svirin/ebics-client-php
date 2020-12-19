<?php

namespace AndrewSvirin\Ebics\Exceptions;

/**
 * TxAbortException used for 091102 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class TxAbortException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091102',
            $responseMessage,
            'If the bank supports transaction recovery, the bank verifies whether ' .
            'an upload transaction can be recovered. If the transaction cannot be recovered, ' .
            'the bank terminates the transaction.'
        );
    }
}
