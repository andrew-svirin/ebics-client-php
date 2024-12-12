<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * UnsupportedRequestForOrderInstanceException used for 090006 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class UnsupportedRequestForOrderInstanceException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '090006',
            $responseMessage,
            'In the case of some business transactions, it is not possible to ' .
            'retrieve detailed information of the order data.'
        );
    }
}
