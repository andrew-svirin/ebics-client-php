<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\BodyHandler;

use AndrewSvirin\Ebics\Handlers\BodyHandler;
use AndrewSvirin\Ebics\Models\Request;
use PHPUnit\Framework\TestCase;

class HandleTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new BodyHandler();

        $request    = new Request();
        $domElement = $request->createElement('test', 'fezfze');
        $request->appendChild($domElement);

        $request = $sUT->handle($request, $domElement, 'test');

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><test>fezfze<body><DataTransfer><OrderData>eJwrSS0uAQAEXQHB</OrderData></DataTransfer></body></test>', $request->saveXML());
    }
}
