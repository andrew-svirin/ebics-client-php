<?php

namespace EbicsApi\Ebics\Services;

use EbicsApi\Ebics\Contracts\HttpClientInterface;
use EbicsApi\Ebics\Models\Http\Response;
use RuntimeException;

/**
 * Http client.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class HttpClient implements HttpClientInterface
{
    protected const CONTENT_TYPE = 'text/xml; charset=UTF-8';

    /**
     * @param string $contents
     * @return Response
     */
    protected function createResponse(string $contents): Response
    {
        if (empty($contents)) {
            throw new RuntimeException('Response is empty.');
        }

        $response = new Response();
        $response->loadXML($contents);

        return $response;
    }
}
