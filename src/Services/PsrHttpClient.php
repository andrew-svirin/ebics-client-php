<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\HttpClientInterface;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use RuntimeException;

class PsrHttpClient implements HttpClientInterface
{
    /** @var ClientInterface */
    private $client;
    /** @var RequestFactoryInterface */
    private $requestFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @inheritDoc
     */
    public function post(string $url, Request $request): Response
    {
        // Construct PSR request
        $psrRequest = $this->requestFactory->createRequest('POST', $url);
        $psrRequest = $psrRequest->withHeader('Content-Type', 'text/xml; charset=UTF-8');

        // Call PSR HTTP client
        $psrResponse = $this->client->sendRequest($psrRequest);
        $contents = $psrResponse->getBody()->getContents();

        if (empty($contents)) {
            throw new RuntimeException('Response is empty.');
        }

        $response = new Response();
        $response->loadXML($contents);

        return $response;
    }
}
