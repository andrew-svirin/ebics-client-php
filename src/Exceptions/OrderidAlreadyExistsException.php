<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * OrderidAlreadyExistsException used for 091115 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class OrderidAlreadyExistsException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091115',
            $responseMessage,
            'The submitted order number already exists.'
        );
    }
}
