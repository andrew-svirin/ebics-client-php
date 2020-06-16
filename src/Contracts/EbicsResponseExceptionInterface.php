<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;

/**
 * EBICS ResponseExceptionInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
interface EbicsResponseExceptionInterface extends \Throwable
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
