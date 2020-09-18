<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\RequestHandler;

use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class BuildINITest extends TestCase
{
    public function testOk(): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyring = self::createMock(KeyRing::class);

        $bank->expects(self::any())->method('isCertified')->willReturn(false);
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');
        $user->expects(self::any())->method('getPartnerId')->willReturn('myPartnerId');
        $user->expects(self::any())->method('getUserId')->willReturn('myUserId');

        $orderData = self::createMock(OrderData::class);
        $orderData->expects(self::once())->method('getContent')->willReturn('test');

        $orderDataHandler = self::createMock(OrderDataHandler::class);
        $orderDataHandler->expects(self::once())->method('handleINI')->willReturn($orderData);

        $sUT = new RequestHandler(null, null, null, $orderDataHandler);

        $expected = '<?xml version=\'1.0\' encoding=\'utf-8\'?>
<ebicsUnsecuredRequest xmlns="urn:org:ebics:H004" Version="H004" Revision="1"><header authenticate="true">
    <static>
        <HostID>myHostId</HostID>
        <PartnerID>myPartnerId</PartnerID>
        <UserID>myUserId</UserID>
        <Product Language="de">Ebics client PHP</Product>
        <OrderDetails>
        <OrderType>INI</OrderType>
        <OrderAttribute>DZNNN</OrderAttribute>
        </OrderDetails>
        <SecurityMedium>0000</SecurityMedium>
    </static>
<mutable/>
</header>
<body>
    <DataTransfer>
        <OrderData>eJwrSS0uAQAEXQHB</OrderData>
    </DataTransfer>
</body>
</ebicsUnsecuredRequest>';

        $certificat = self::createMock(Certificate::class);

        self::assertXmlStringEqualsXmlString($expected, $sUT->buildINI($bank, $user, $keyring, $certificat, new DateTime())->getContent());
    }
}
