<?php

namespace AndrewSvirin\Ebics\Exceptions;

/**
 * DuplicateSignatureException used for 091306 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class DuplicateSignatureException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct('091306', $responseMessage, 'The signatory has already signed the order.');
    }
}
