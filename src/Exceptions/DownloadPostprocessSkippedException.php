<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * DownloadPostprocessSkippedException used for 011001 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class DownloadPostprocessSkippedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '011001',
            $responseMessage,
            'The negative acknowledgment of the EBICS response that is ' .
            'sent to the client from the server.'
        );
    }
}
