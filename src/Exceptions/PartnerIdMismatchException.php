<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * PartnerIdMismatchException used for 091120 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class PartnerIdMismatchException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091120',
            $responseMessage,
            'The partner ID of the electronic signature file differs from the partner ID of the submitter.'
        );
    }
}
