<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * ProcessingErrorException used for 091116 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class ProcessingErrorException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091116',
            $responseMessage,
            'When processing an EBICS request, other business-related errors occurred.'
        );
    }
}
