<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * OrderidUnknownException used for 091114 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class OrderidUnknownException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091114',
            $responseMessage,
            'Upon verification, the bank finds that the order is not located in the VEU processing system.'
        );
    }
}
