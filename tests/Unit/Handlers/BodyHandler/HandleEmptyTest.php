<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\BodyHandler;

use AndrewSvirin\Ebics\Handlers\BodyHandler;
use AndrewSvirin\Ebics\Models\Request;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass BodyHandler
 */
class HandleEmptyTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new BodyHandler();

        $domDocument = new Request();
        $domElement  = $domDocument->createElement('test', 'fezfze');
        $domDocument->appendChild($domElement);

        $domDocument = $sUT->handleEmpty($domDocument, $domElement);

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><test>fezfze<body/></test>', (string) $domDocument->saveXML());
    }
}
