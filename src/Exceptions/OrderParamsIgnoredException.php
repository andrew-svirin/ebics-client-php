<?php

namespace AndrewSvirin\Ebics\Exceptions;

/**
 * OrderParamsIgnoredException used for 031001 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class OrderParamsIgnoredException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct('031001', $responseMessage, 'The supplied order parameters that are not supported by the bank are ignored.');
    }
}
