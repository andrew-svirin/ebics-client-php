<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * InvalidRequestException used for 061002 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class InvalidRequestException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '061002',
            $responseMessage,
            'The received EBICS XML message does not conform to the EBICS specifications.'
        );
    }
}
