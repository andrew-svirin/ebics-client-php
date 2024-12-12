<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * InvalidXmlException used for 091010 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class InvalidXmlException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091010',
            $responseMessage,
            'The XML schema does not conform to the EBICS specifications.'
        );
    }
}
