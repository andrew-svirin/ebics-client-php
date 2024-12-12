<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * InvalidRequestContentException used for 091113 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class InvalidRequestContentException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091113',
            $responseMessage,
            'The EBICS request does not conform to the XML schema definition specified for individual requests.'
        );
    }
}
