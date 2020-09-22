<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\EbicsRequestHandler;

use AndrewSvirin\Ebics\Handlers\EbicsRequestHandler;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass EbicsRequestHandler
 */
class HandleHEVTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new EbicsRequestHandler();

        $domDocument = new DOMDocument();

        $domElement = $sUT->handleHEV($domDocument);

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><ebicsHEVRequest xmlns="http://www.ebics.org/H000" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.ebics.org/H000 http://www.ebics.org/H000/ebics_hev.xsd"/>', (string) $domDocument->saveXML());

        $domElement->nodeValue = 'test!';

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><ebicsHEVRequest xmlns="http://www.ebics.org/H000" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.ebics.org/H000 http://www.ebics.org/H000/ebics_hev.xsd">test!</ebicsHEVRequest>', (string) $domDocument->saveXML());
    }
}
