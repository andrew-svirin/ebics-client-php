<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * RecoveryNotSupportedException used for 091105 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class RecoveryNotSupportedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091105',
            $responseMessage,
            'If the bank does not support transaction recovery, ' .
            'the upload transaction is terminated.'
        );
    }
}
