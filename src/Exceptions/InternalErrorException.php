<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * InternalErrorException used for 061099 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class InternalErrorException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '061099',
            $responseMessage,
            'An internal error occurred when processing an EBICS request.'
        );
    }
}
