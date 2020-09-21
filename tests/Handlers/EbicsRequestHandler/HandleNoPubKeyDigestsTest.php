<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\EbicsRequestHandler;

use AndrewSvirin\Ebics\Handlers\EbicsRequestHandler;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class HandleNoPubKeyDigestsTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new EbicsRequestHandler();

        $domDocument = new DOMDocument();

        $domElement = $sUT->handleNoPubKeyDigests($domDocument);

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><ebicsNoPubKeyDigestsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Revision="1" Version="H004"/>', (string) $domDocument->saveXML());

        $domElement->nodeValue = 'test!';

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><ebicsNoPubKeyDigestsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Revision="1" Version="H004">test!</ebicsNoPubKeyDigestsRequest>', (string) $domDocument->saveXML());
    }
}
