<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\RequestHandler;

use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use PHPUnit\Framework\TestCase;

class BuildHEVTest extends TestCase
{
    public function testOk(): void
    {
        $bank    = self::createMock(Bank::class);
        $user    = self::createMock(User::class);
        $keyring = self::createMock(KeyRing::class);

        $bank->expects(self::once())->method('getHostId')->willReturn('myHostId');

        $sUT = new RequestHandler();

        $expected = '<?xml version=\'1.0\' encoding=\'utf-8\'?>
<ebicsHEVRequest xmlns="http://www.ebics.org/H000" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="http://www.ebics.org/H000 http://www.ebics.org/H000/ebics_hev.xsd">
    <HostID>myHostId</HostID>
</ebicsHEVRequest>';

        self::assertXmlStringEqualsXmlString($expected, $sUT->buildHEV($bank)->getContent());
    }
}
