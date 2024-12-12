<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * NoOnlineChecksException used for 011301 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class NoOnlineChecksException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '011301',
            $responseMessage,
            'The bank does not principally support preliminary verification of orders but ' .
            'the EBICS request contains data for preliminary verification of the order.'
        );
    }
}
