<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\HttpClientInterface;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;
use RuntimeException;

/**
 * Http client.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
class HttpClient implements HttpClientInterface
{

    /**
     * @param string $url
     * @param Request $request
     *
     * @return Response
     */
    public function post(string $url, Request $request): Response
    {
        $body = $request->getContent();

        $ch = curl_init($url);
        if (false === $ch) {
            throw new RuntimeException('Can not create curl.');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/xml; charset=ISO-8859-1',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $httpResponse = curl_exec($ch);
        curl_close($ch);

        if (!is_string($httpResponse)) {
            throw new RuntimeException('Response is empty.');
        }

        $response = new Response();
        $response->loadXML($httpResponse);

        return $response;
    }
}
