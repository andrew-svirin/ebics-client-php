<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * X509InvalidThumbprintException used for 091212 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class X509InvalidThumbprintException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091212',
            $responseMessage,
            'The thumb print does not correspond to the certificate.'
        );
    }
}
