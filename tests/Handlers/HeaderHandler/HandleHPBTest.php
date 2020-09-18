<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\RequestHandler;

use AndrewSvirin\Ebics\Handlers\HeaderHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\DOMDocument;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use PHPUnit\Framework\TestCase;

class HandleHPBTest extends TestCase
{
    public function testOk(): void
    {
        $bank = self::createMock(Bank::class);
        $user = self::createMock(User::class);

        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');
        $user->expects(self::any())->method('getPartnerId')->willReturn('myPartnerId');
        $user->expects(self::any())->method('getUserId')->willReturn('myUserId');

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::once())->method('generateNonce')->willReturn('myNonce');

        $sUT = new HeaderHandler($cryptService);

        $expected = '<?xml version="1.0" encoding="utf-8"?>
<test>
    <header authenticate="true">
        <static>
            <HostID>myHostId</HostID>
            <Nonce>myNonce</Nonce>
            <Timestamp>2010-10-10T10:10:10Z</Timestamp>
            <PartnerID>myPartnerId</PartnerID>
            <UserID>myUserId</UserID>
            <Product Language="de">Ebics client PHP</Product>
            <OrderDetails>
                <OrderType>HPB</OrderType>
                <OrderAttribute>DZHNN</OrderAttribute>
            </OrderDetails>
            <SecurityMedium>0000</SecurityMedium>
        </static>
        <mutable/>
    </header>
</test>
        ';

        $domDocument = new DOMDocument();
        $domElement  = $domDocument->createElement('test');
        $domDocument->appendChild($domElement);

        $returned = $sUT->handleHPB($bank, $user, $domDocument, $domElement, new DateTime('2010-10-10 10:10:10'));

        self::assertXmlStringEqualsXmlString($expected, $returned->saveXML());
    }
}
