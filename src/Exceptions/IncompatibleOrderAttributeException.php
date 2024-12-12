<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * IncompatibleOrderAttributeException used for 091121 EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class IncompatibleOrderAttributeException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091121',
            $responseMessage,
            'The specified order attribute is not compatible with the order in the bank system. ' .
            'If the bank has a file with the attribute DZHNN or other electronic signature files ' .
            '(for example, with the attribute UZHNN) for the same order, then the use of the order ' .
            'attributes DZHNN is not allowed. Also, if the bank already has the same order and the ' .
            'order was transmitted with the order attributes DZHNN, then again the use of the order ' .
            'attributes DZHNN is not allowed.'
        );
    }
}
