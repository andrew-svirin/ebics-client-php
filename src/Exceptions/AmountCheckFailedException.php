<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * AmountCheckFailedException used for 091303 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class AmountCheckFailedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091303',
            $responseMessage,
            'Preliminary verification of the account amount limit has failed.'
        );
    }
}
