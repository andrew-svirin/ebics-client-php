<?php

namespace EbicsApi\Ebics\Contracts;

use EbicsApi\Ebics\Models\Http\Request;
use EbicsApi\Ebics\Models\Http\Response;

/**
 * EBICS ResponseExceptionInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
interface EbicsResponseExceptionInterface
{
    /**
     * Returns the EBICS error code
     *
     * @return string
     */
    public function getResponseCode(): string;

    /**
     * Returns the EBICS error meaning if available
     *
     * @return string|null
     */
    public function getMeaning(): ?string;

    /**
     * Returns the request which caused this error
     *
     * @return Request|null
     */
    public function getRequest(): ?Request;

    /**
     * Returns the full response from the bank server
     *
     * @return Response|null
     */
    public function getResponse(): ?Response;
}
