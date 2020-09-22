<?php


namespace AndrewSvirin\Ebics;


use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestMaker
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Make request to bank server.
     *
     * @throws TransportExceptionInterface
     */
    public function post(Request $request, Bank $bank): Response
    {
        return new Response($this->httpClient->request('POST', $bank->getUrl(), [
            'headers' => [
                'Content-Type' => 'text/xml; charset=ISO-8859-1',
            ],
            'body' => $request->getContent(),
            'verify_peer' => false,
            'verify_host' => false,
        ])->getContent(), $bank->getVersion());
    }
}