<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * InvalidHostIdException used for 091011 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class InvalidHostIdException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091011',
            $responseMessage,
            'The transmitted host ID is not known to the bank.'
        );
    }
}
