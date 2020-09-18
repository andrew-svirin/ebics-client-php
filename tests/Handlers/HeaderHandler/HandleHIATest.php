<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\RequestHandler;

use AndrewSvirin\Ebics\Handlers\HeaderHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\DOMDocument;
use AndrewSvirin\Ebics\Models\User;
use PHPUnit\Framework\TestCase;

class HandleHIATest extends TestCase
{
    public function testOk(): void
    {
        $bank = self::createMock(Bank::class);
        $user = self::createMock(User::class);

        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');
        $user->expects(self::any())->method('getPartnerId')->willReturn('myPartnerId');
        $user->expects(self::any())->method('getUserId')->willReturn('myUserId');

        $sUT = new HeaderHandler();

        $expected = '<?xml version="1.0" encoding="utf-8"?>
<test><header authenticate="true"><static><HostID>myHostId</HostID><PartnerID>myPartnerId</PartnerID><UserID>myUserId</UserID><Product Language="de">Ebics client PHP</Product><OrderDetails><OrderType>HIA</OrderType><OrderAttribute>DZNNN</OrderAttribute></OrderDetails><SecurityMedium>0000</SecurityMedium></static><mutable/></header></test>
        ';

        $domDocument = new DOMDocument();
        $domElement  = $domDocument->createElement('test');
        $domDocument->appendChild($domElement);

        $returned = $sUT->handleHIA($bank, $user, $domDocument, $domElement);

        self::assertXmlStringEqualsXmlString($expected, $returned->saveXML());
    }
}
