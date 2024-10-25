<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Exceptions\EbicsErrorCodeMapping;
use AndrewSvirin\Ebics\Exceptions\IncorrectResponseEbicsException;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;

/**
 * Exception factory with an EBICS response code
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
final class EbicsExceptionFactory
{
    /**
     * @param string $errorCode
     * @param string|null $errorText
     * @param Request|null $request
     * @param Response|null $response
     *
     * @return void
     * @throws IncorrectResponseEbicsException
     */
    public static function buildExceptionFromCode(
        string $errorCode,
        ?string $errorText = null,
        ?Request $request = null,
        ?Response $response = null
    ): void {
        if (($exceptionClass = EbicsErrorCodeMapping::resolveClass($errorCode))) {
            $exception = new $exceptionClass($errorText);
        } else {
            throw new IncorrectResponseEbicsException(sprintf(
                'Incorrect Response Exception %s %s',
                $errorCode,
                $errorText
            ));
        }

        if (null !== $request) {
            $exception->setRequest($request);
        }

        if (null !== $response) {
            $exception->setResponse($response);
        }

        throw $exception;
    }
}
