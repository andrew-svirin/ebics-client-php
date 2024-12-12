<?php

namespace EbicsApi\Ebics\Services;

use EbicsApi\Ebics\Models\Http\Request;
use EbicsApi\Ebics\Models\Http\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * PSR http client.
 *
 * This client allows to use a PSR http client instead of the internal HttpClient.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Ronan GIRON <https://github.com/ElGigi>
 */
final class PsrHttpClient extends HttpClient
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @inheritDoc
     * @throws ClientExceptionInterface
     */
    public function post(string $url, Request $request): Response
    {
        // Construct PSR request
        $psrRequest = $this->requestFactory
            ->createRequest('POST', $url)
            ->withHeader('Content-Type', self::CONTENT_TYPE)
            ->withBody($this->streamFactory->createStream($request->getContent()));

        // Call PSR HTTP client
        $psrResponse = $this->client->sendRequest($psrRequest);
        $contents = $psrResponse->getBody()->getContents();

        return $this->createResponse($contents);
    }
}
