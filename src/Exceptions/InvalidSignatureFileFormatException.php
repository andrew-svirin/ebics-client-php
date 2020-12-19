<?php

namespace AndrewSvirin\Ebics\Exceptions;

/**
 * InvalidSignatureFileFormatException used for 091111 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class InvalidSignatureFileFormatException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091111',
            $responseMessage,
            'The submitted electronic signature file does not conform to the defined format.'
        );
    }
}
