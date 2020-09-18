<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\BodyHandler;

use AndrewSvirin\Ebics\Handlers\BodyHandler;
use AndrewSvirin\Ebics\Models\Request;
use PHPUnit\Framework\TestCase;

class HandleTransferReceiptTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new BodyHandler();

        $request    = new Request();
        $domElement = $request->createElement('test', 'fezfze');
        $request->appendChild($domElement);

        $request = $sUT->handleTransferReceipt($request, $domElement, 10);

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><test>fezfze<body><TransferReceipt authenticate="true"><ReceiptCode>10</ReceiptCode></TransferReceipt></body></test>', $request->saveXML());
    }
}
