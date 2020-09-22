<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\EbicsRequestHandler;

use AndrewSvirin\Ebics\Handlers\EbicsRequestHandler;
use AndrewSvirin\Ebics\Models\Bank;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass EbicsRequestHandler
 */
class HandleSecuredTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new EbicsRequestHandler();

        $domDocument = new DOMDocument();
        $bank        = new Bank('test', 'test', false);

        $domElement = $sUT->handleSecured($domDocument, $bank);

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><ebicsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Revision="1" Version="H004"/>', (string) $domDocument->saveXML());

        $domElement->nodeValue = 'test!';

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><ebicsRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Revision="1" Version="H004">test!</ebicsRequest>', (string) $domDocument->saveXML());
    }
}
