<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\RequestHandler;

use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Models\Version;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass RequestHandler
 */
class BuildHIATest extends TestCase
{
    public function securedOrNot(): iterable
    {
        yield [true];
        yield [false];
    }

    /** @dataProvider securedOrNot */
    public function testOk(bool $secured): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyring = self::createMock(KeyRing::class);

        $bank->expects(self::any())->method('isCertified')->willReturn($secured);
        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');
        $bank->expects(self::any())->method('getVersion')->willReturn(Version::V25);

        $orderData = self::createMock(OrderData::class);
        $orderData->expects(self::once())->method('getContent')->willReturn('test');

        $orderDataHandler = self::createMock(OrderDataHandler::class);
        $orderDataHandler->expects(self::once())->method('handleHIA')->willReturn($orderData);

        $sUT = new RequestHandler(null, null, null, $orderDataHandler);

        $expected = '<?xml version=\'1.0\' encoding=\'utf-8\'?>
<ebicsUnsecuredRequest xmlns="urn:org:ebics:H004" Version="H004" Revision="1">
<header authenticate="true">
<static>
<HostID>myHostId</HostID>
<PartnerID></PartnerID>
<UserID></UserID>
<Product Language="de">Ebics client PHP</Product>
<OrderDetails>
<OrderType>HIA</OrderType>
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

        $certificatA = self::createMock(Certificate::class);
        $certificatX = self::createMock(Certificate::class);

        self::assertXmlStringEqualsXmlString($expected, $sUT->buildHIA($bank, $user, $keyring, $certificatA, $certificatX, new DateTime())->getContent());
    }
}
