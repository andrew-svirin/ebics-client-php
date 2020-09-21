<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\HostHandler;

use AndrewSvirin\Ebics\Handlers\HostHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Request;
use PHPUnit\Framework\TestCase;

class HandleTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new HostHandler();

        $request    = new Request();
        $domElement = $request->createElement('test', 'fezfze');
        $request->appendChild($domElement);

        $bank = self::createMock(Bank::class);
        $bank->expects(self::once())->method('getHostId')->willReturn('myId');

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?><test>fezfze<HostID>myId</HostID></test>', $sUT->handle($bank, $request, $domElement));
    }
}
