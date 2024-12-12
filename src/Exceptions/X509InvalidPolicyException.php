<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * X509InvalidPolicyException used for 091215 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class X509InvalidPolicyException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091215',
            $responseMessage,
            'The certificate has invalid policy when determining certificate verification.'
        );
    }
}
