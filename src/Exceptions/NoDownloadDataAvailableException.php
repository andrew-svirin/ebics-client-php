<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * NoDownloadDataAvailableException used for 090005 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class NoDownloadDataAvailableException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '090005',
            $responseMessage,
            'If the requested download data is not available, the EBICS transaction is terminated.'
        );
    }
}
