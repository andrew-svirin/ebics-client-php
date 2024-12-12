<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * DownloadSignedOnlyException used for 091001 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class DownloadSignedOnlyException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091001',
            $responseMessage,
            'The bank system only supports bank-technically signed download ' .
            'order data for the order request. If the subscriber sets the order attributes ' .
            'to DZHNN and requests the download data without the electronic signature of ' .
            'the bank, the transaction initialization is terminated.'
        );
    }
}
