<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * DistributedSignatureAuthorisationFailedException used for 091007 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class DistributedSignatureAuthorisationFailedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091007',
            $responseMessage,
            'Subscriber possesses no authorization of signature for ' .
            'the referenced order in the VEU administration.'
        );
    }
}
