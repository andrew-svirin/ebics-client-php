<?php

namespace AndrewSvirin\Ebics\Exceptions;

/**
 * MaxOrderDataSizeExceededException used for 091117 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class MaxOrderDataSizeExceededException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct('091117', $responseMessage, 'The bank does not support the requested order size.');
    }
}
