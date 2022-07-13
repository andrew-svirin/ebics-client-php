<?php

namespace AndrewSvirin\Ebics\Tests\Services;

use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Services\PsrHttpClient;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;
use Berlioz\Http\Message\HttpFactory;
use Berlioz\Http\Message\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PsrHttpClientTest extends AbstractEbicsTestCase
{
    public function testPost(): void
    {
        $client = new PsrHttpClient(
            $psrClient = new class implements ClientInterface {
                /** @var RequestInterface */
                public $request;

                public function sendRequest(RequestInterface $request): ResponseInterface
                {
                    $this->request = $request;
                    return new Response("<?xml version='1.0' encoding='utf-8'?><ResponseTest/>");
                }
            },
            new HttpFactory(),
            new HttpFactory()
        );

        $request = new Request();
        $request->loadXML("<?xml version='1.0' encoding='utf-8'?><RequestTest/>");
        $response = $client->post('fake', $request);

        $this->assertEquals(
            "<?xml version='1.0' encoding='utf-8'?><ResponseTest/>",
            $response->getContent()
        );
        $this->assertEquals(
            ['Content-Type' => ['text/xml; charset=UTF-8']],
            $psrClient->request->getHeaders()
        );
        $this->assertEquals(
            $request->getContent(),
            $psrClient->request->getBody()->getContents()
        );
    }
}
