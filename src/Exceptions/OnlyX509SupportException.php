<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * OnlyX509SupportException used for 091217 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class OnlyX509SupportException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091217',
            $responseMessage,
            'The bank supports evaluation of X.509 data only.'
        );
    }
}
