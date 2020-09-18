<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\RequestMaker;

use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\RequestMaker;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PostTest extends TestCase
{
    public function testOk(): void
    {
        $reponse    = self::createMock(ResponseInterface::class);
        $httpClient = self::createMock(HttpClientInterface::class);

        $reponse->expects(self::once())->method('getContent')->willReturn('<?xml version="1.0" encoding="UTF-8"?><test/>');
        $httpClient->expects(self::once())->method('request')->with('POST', 'url', [
            'headers' => ['Content-Type' => 'text/xml; charset=ISO-8859-1'],
            'body' => 'content',
            'verify_peer' => false,
            'verify_host' => false,
        ])->willReturn($reponse);

        $sUT = new RequestMaker($httpClient);

        $request = self::createMock(Request::class);
        $bank    = self::createMock(Bank::class);

        $bank->expects(self::once())->method('getUrl')->willReturn('url');
        $request->expects(self::once())->method('getContent')->willReturn('content');

        self::assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="UTF-8"?><test/>', $sUT->post($request, $bank)->getContent());
    }
}
