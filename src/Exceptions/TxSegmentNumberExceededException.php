<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * TxSegmentNumberExceededException used for 091104 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class TxSegmentNumberExceededException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091104',
            $responseMessage,
            'The serial number of the transmitted order data segment must be ' .
            'less than or equal to the total number of data segments that are to be transmitted. ' .
            'The transaction is terminated if the number of transmitted order ' .
            'data segments exceeds the total number of data segments.'
        );
    }
}
