<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\EbicsRequestHandler;

use AndrewSvirin\Ebics\Handlers\EbicsRequestHandler;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class HandleUnsecuredTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new EbicsRequestHandler();

        $domDocument = new DOMDocument();

        $domElement = $sUT->handleUnsecured($domDocument);

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><ebicsUnsecuredRequest xmlns="urn:org:ebics:H004" Revision="1" Version="H004"/>', (string) $domDocument->saveXML());

        $domElement->nodeValue = 'test!';

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><ebicsUnsecuredRequest xmlns="urn:org:ebics:H004" Revision="1" Version="H004">test!</ebicsUnsecuredRequest>', (string) $domDocument->saveXML());
    }
}
