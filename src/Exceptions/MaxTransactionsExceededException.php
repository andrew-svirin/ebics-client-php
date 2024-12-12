<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * MaxTransactionsExceededException used for 091119 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class MaxTransactionsExceededException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091119',
            $responseMessage,
            'The maximum number of parallel transactions per customer is exceeded.'
        );
    }
}
