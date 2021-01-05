<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Exceptions\EbicsErrorCodeMapping;
use AndrewSvirin\Ebics\Exceptions\EbicsResponseException;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;

/**
 * Exception factory with an EBICS response code
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class EbicsExceptionFactory
{

    /**
     * @param string $errorCode
     * @param string|null $errorText
     * @param Request|null $request
     * @param Response|null $response
     *
     * @return void
     * @throws EbicsResponseException
     */
    public static function buildExceptionFromCode(
        string $errorCode,
        ?string $errorText = null,
        ?Request $request = null,
        ?Response $response = null
    ): void {
        if (!empty(EbicsErrorCodeMapping::$mapping[$errorCode])) {
            $exceptionClass = EbicsErrorCodeMapping::$mapping[$errorCode];

            $exception = new $exceptionClass($errorText);
        } else {
            $exception = new EbicsResponseException($errorCode, $errorText);
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
