<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * InvalidSignerStateException used for 091305 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class InvalidSignerStateException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091305',
            $responseMessage,
            'The state of the signatory is not admissible.'
        );
    }
}
