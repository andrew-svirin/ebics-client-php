<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * DownloadPostprocessDoneException used for 011000 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class DownloadPostprocessDoneException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '011000',
            $responseMessage,
            'The positive acknowledgment of the EBICS response that is ' .
            'sent to the client from the server.'
        );
    }
}
