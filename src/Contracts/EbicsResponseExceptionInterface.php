<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;

interface EbicsResponseExceptionInterface
{
    /**
     * Returns the EBICS error code
     */
    public function getResponseCode(): string;

    /**
     * Returns the EBICS error meaning if available
     */
    public function getMeaning(): ?string;

    /**
     * Returns the request which caused this error
     */
    public function getRequest(): ?Request;

    /**
     * Returns the full response from the bank server
     */
    public function getResponse(): ?Response;
}
