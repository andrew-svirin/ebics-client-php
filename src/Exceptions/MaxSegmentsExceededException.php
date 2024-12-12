<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * MaxSegmentsExceededException used for 091118 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class MaxSegmentsExceededException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091118',
            $responseMessage,
            'The submitted number of segments for upload is very high.'
        );
    }
}
