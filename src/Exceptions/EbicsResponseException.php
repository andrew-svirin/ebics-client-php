<?php

namespace AndrewSvirin\Ebics\Exceptions;

use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;

class EbicsResponseException extends EbicsException implements EbicsResponseExceptionInterface
{
    /** @var string */
    private $responseCode;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var string|null */
    private $meaning;

    public function __construct(string $responseCode, ?string $responseMessage, ?string $meaning = null)
    {
        parent::__construct($responseMessage ?: $meaning, (int) $responseCode);

        $this->responseCode = $responseCode;
        $this->meaning = $meaning;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getMeaning(): ?string
    {
        return $this->meaning;
    }

    public function getResponseCode(): string
    {
        return $this->responseCode;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
