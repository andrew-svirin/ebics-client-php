<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\HttpClientInterface;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;
use RuntimeException;

/**
 * Curl Http client.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class CurlHttpClient extends HttpClient implements HttpClientInterface
{

    private array $curlOpts;
    
    public function __construct(array $curlOpts = [])
    {
        $this->curlOpts = $curlOpts;
    }

    /**
     * @inheritDoc
     */
    public function post(string $url, Request $request): Response
    {
        $body = $request->getContent();

        $ch = curl_init($url);
        if (false === $ch) {
            throw new RuntimeException('Can not create curl.');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: ' . self::CONTENT_TYPE,
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        foreach ($this->curlOpts as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
        
        $contents = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
        }
        curl_close($ch);

        if (!is_string($contents)) {
            throw new RuntimeException('Response is not a string. '. ($errorMsg ?? ''));
        }

        return $this->createResponse($contents);
    }
}
