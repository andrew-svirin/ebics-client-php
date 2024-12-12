<?php

namespace EbicsApi\Ebics\Contracts;

use EbicsApi\Ebics\Models\Http\Request;
use EbicsApi\Ebics\Models\Http\Response;

/**
 * EBICS http client representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface HttpClientInterface
{
    /**
     * Make post request and transform to xml.
     *
     * @param string $url
     * @param Request $request
     *
     * @return Response
     */
    public function post(string $url, Request $request): Response;
}
