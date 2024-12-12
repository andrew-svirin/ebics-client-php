<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * InvalidOrderDataFormatException used for 090004 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class InvalidOrderDataFormatException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '090004',
            $responseMessage,
            'The order data does not correspond with the designated format.'
        );
    }
}
