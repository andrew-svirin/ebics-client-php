<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * X509InvalidBasicConstraintsException used for 091216 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class X509InvalidBasicConstraintsException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091216',
            $responseMessage,
            'The basic constraints are not valid when determining certificate verification.'
        );
    }
}
