<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\E2e\EbicsClient;

use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Models\Version;
use AndrewSvirin\Ebics\Tests\E2e\Base;
use DateTime;

/**
 * @coversNothing
 */
class HIATest extends Base
{
    public function certified(): iterable
    {
        yield [
            false,
            '<?xml version="1.0"?>
<ebicsUnsecuredRequest xmlns="urn:org:ebics:H004" Revision="1" Version="H004">
  <header authenticate="true">
    <static>
      <HostID>myHostId</HostID>
      <PartnerID>myPartId</PartnerID>
      <UserID>myUserId</UserID>
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
      <OrderData>eJxlkd9PgzAQx9/9Kwg+8DR6zMQoKV2WsBhijEYmD751a4dNoCX9IZt/vQwIDpfcw933Pv3etcWrY11531wboWQSRCEEHpd7xYQsk8DZw+IhWJEbnItSUus0f3O7Z3561YzrlFrqdcelSfwva5sYobZtQ74TexMqXaIcIPIHImZzqL3riSUAIHhEHcOMKG998n9SJg+K4CEvaOU4wczE7/l6Vr8o5ipnCHsqfli6STC6EM/A5tgoyaUdiWwgJrWvZqZbUfPc0rohS4hg0cc2griPT4z++hjNtpv2L4ZHJWuAe4yu5AtpdlOqreQ6S0l9OucZ6/wnDX+YsdcnXW8UrtymHyK/VEKnZg==</OrderData>
    </DataTransfer>
  </body>
</ebicsUnsecuredRequest>',
            '<?xml version="1.0" encoding="UTF-8" ?>
        <ebicsKeyManagementResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H004" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd">
            <header xmlns="urn:org:ebics:H004" authenticate="true">
                <static/>
                <mutable xmlns="urn:org:ebics:H004">
                    <ReturnCode xmlns="urn:org:ebics:H004">000000</ReturnCode>
                    <ReportText xmlns="urn:org:ebics:H004">hello</ReportText>
                </mutable>
            </header>
            <body xmlns="urn:org:ebics:H004">
                <ReturnCode authenticate="true" xmlns="urn:org:ebics:H004">000000</ReturnCode>
            </body>
        </ebicsKeyManagementResponse>',
            Version::V25,
        ];

        yield [
            true,
            '<?xml version="1.0"?>
<ebicsUnsecuredRequest xmlns="urn:org:ebics:H004" Revision="1" Version="H004">
  <header authenticate="true">
    <static>
      <HostID>myHostId</HostID>
      <PartnerID>myPartId</PartnerID>
      <UserID>myUserId</UserID>
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
      <OrderData>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</OrderData>
    </DataTransfer>
  </body>
</ebicsUnsecuredRequest>',
            '<?xml version="1.0" encoding="UTF-8" ?>
        <ebicsKeyManagementResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H004" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd">
            <header xmlns="urn:org:ebics:H004" authenticate="true">
                <static/>
                <mutable xmlns="urn:org:ebics:H004">
                    <ReturnCode xmlns="urn:org:ebics:H004">000000</ReturnCode>
                    <ReportText xmlns="urn:org:ebics:H004">hello</ReportText>
                </mutable>
            </header>
            <body xmlns="urn:org:ebics:H004">
                <ReturnCode authenticate="true" xmlns="urn:org:ebics:H004">000000</ReturnCode>
            </body>
        </ebicsKeyManagementResponse>',
            Version::V25,
        ];

        yield [
            false,
            '<?xml version="1.0"?>
<ebicsUnsecuredRequest xmlns="urn:org:ebics:H004" Revision="1" Version="H004">
  <header authenticate="true">
    <static>
      <HostID>myHostId</HostID>
      <PartnerID>myPartId</PartnerID>
      <UserID>myUserId</UserID>
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
      <OrderData>eJxlkd9PgzAQx9/9Kwg+8DR6zMQoKV2WsBhijEYmD751a4dNoCX9IZt/vQwIDpfcw933Pv3etcWrY11531wboWQSRCEEHpd7xYQsk8DZw+IhWJEbnItSUus0f3O7Z3561YzrlFrqdcelSfwva5sYobZtQ74TexMqXaIcIPIHImZzqL3riSUAIHhEHcOMKG998n9SJg+K4CEvaOU4wczE7/l6Vr8o5ipnCHsqfli6STC6EM/A5tgoyaUdiWwgJrWvZqZbUfPc0rohS4hg0cc2griPT4z++hjNtpv2L4ZHJWuAe4yu5AtpdlOqreQ6S0l9OucZ6/wnDX+YsdcnXW8UrtymHyK/VEKnZg==</OrderData>
    </DataTransfer>
  </body>
</ebicsUnsecuredRequest>',
            '<?xml version="1.0" encoding="UTF-8" ?>
        <ebicsKeyManagementResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H004" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd">
            <header xmlns="urn:org:ebics:H004" authenticate="true">
                <static/>
                <mutable xmlns="urn:org:ebics:H004">
                    <ReturnCode xmlns="urn:org:ebics:H004">000000</ReturnCode>
                    <ReportText xmlns="urn:org:ebics:H004">hello</ReportText>
                </mutable>
            </header>
            <body xmlns="urn:org:ebics:H004">
                <ReturnCode authenticate="true" xmlns="urn:org:ebics:H004">000000</ReturnCode>
            </body>
        </ebicsKeyManagementResponse>',
            Version::V25,
        ];

        yield [
            false,
            '<?xml version="1.0"?>
<ebicsUnsecuredRequest xmlns="http://www.ebics.org/H003" Revision="1" Version="H003">
  <header authenticate="true">
    <static>
      <HostID>myHostId</HostID>
      <PartnerID>myPartId</PartnerID>
      <UserID>myUserId</UserID>
      <Product Language="de">Ebics client PHP</Product>
      <OrderDetails>
        <OrderType>HIA</OrderType>
        <OrderID>A102</OrderID>
        <OrderAttribute>DZNNN</OrderAttribute>
      </OrderDetails>
      <SecurityMedium>0000</SecurityMedium>
    </static>
    <mutable/>
  </header>
  <body>
    <DataTransfer>
      <OrderData>eJxlkd9PgzAQx9/9Kwg+8DR6zMQoKV2WsBhijEYmD751a4dNoCX9IZt/vQwIDpfcw933Pv3etcWrY11531wboWQSRCEEHpd7xYQsk8DZw+IhWJEbnItSUus0f3O7Z3561YzrlFrqdcelSfwva5sYobZtQ74TexMqXaIcIPIHImZzqL3riSUAIHhEHcOMKG998n9SJg+K4CEvaOU4wczE7/l6Vr8o5ipnCHsqfli6STC6EM/A5tgoyaUdiWwgJrWvZqZbUfPc0rohS4hg0cc2griPT4z++hjNtpv2L4ZHJWuAe4yu5AtpdlOqreQ6S0l9OucZ6/wnDX+YsdcnXW8UrtymHyK/VEKnZg==</OrderData>
    </DataTransfer>
  </body>
</ebicsUnsecuredRequest>',
            '<?xml version="1.0" encoding="UTF-8" ?>
        <ebicsKeyManagementResponse xmlns="http://www.ebics.org/H003" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H003">
            <header xmlns="http://www.ebics.org/H003" authenticate="true">
                <static/>
                <mutable xmlns="http://www.ebics.org/H003">
                    <ReturnCode xmlns="http://www.ebics.org/H003">000000</ReturnCode>
                    <ReportText xmlns="http://www.ebics.org/H003">hello</ReportText>
                </mutable>
            </header>
            <body xmlns="http://www.ebics.org/H003">
                <ReturnCode authenticate="true" xmlns="http://www.ebics.org/H003">000000</ReturnCode>
            </body>
        </ebicsKeyManagementResponse>',
            Version::V24,
        ];

        yield [
            false,
            '<?xml version="1.0"?>
<ebicsUnsecuredRequest xmlns="urn:org:ebics:H005" Revision="1" Version="H005">
  <header authenticate="true">
    <static>
      <HostID>myHostId</HostID>
      <PartnerID>myPartId</PartnerID>
      <UserID>myUserId</UserID>
      <Product Language="de">Ebics client PHP</Product>
      <OrderDetails>
        <AdminOrderType>HIA</AdminOrderType>
      </OrderDetails>
      <SecurityMedium>0000</SecurityMedium>
    </static>
    <mutable/>
  </header>
  <body>
    <DataTransfer>
      <OrderData>eJxlkd9PgzAQx9/9Kwg+8DR6zMQoKV2WsBhijEYmD751a4dNoCX9IZt/vQwIDpfcw933Pv3etcWrY11531wboWQSRCEEHpd7xYQsk8DZw+IhWJEbnItSUus0f3O7Z3561YzrlFrqdcelSfwva5sYobZtQ74TexMqXaIcIPIHImZzqL3riSUAIHhEHcOMKG998n9SJg+K4CEvaOU4wczE7/l6Vr8o5ipnCHsqfli6STC6EM/A5tgoyaUdiWwgJrWvZqZbUfPc0rohS4hg0cc2griPT4z++hjNtpv2L4ZHJWuAe4yu5AtpdlOqreQ6S0l9OucZ6/wnDX+YsdcnXW8UrtymHyK/VEKnZg==</OrderData>
    </DataTransfer>
  </body>
</ebicsUnsecuredRequest>',
            '<?xml version="1.0" encoding="UTF-8" ?>
        <ebicsKeyManagementResponse xmlns="urn:org:ebics:H005" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H005" xsi:schemaLocation="urn:org:ebics:H005 ebics_keymgmt_response_H005.xsd">
            <header xmlns="urn:org:ebics:H005" authenticate="true">
                <static/>
                <mutable xmlns="urn:org:ebics:H005">
                    <ReturnCode xmlns="urn:org:ebics:H005">000000</ReturnCode>
                    <ReportText xmlns="urn:org:ebics:H005">hello</ReportText>
                </mutable>
            </header>
            <body xmlns="urn:org:ebics:H005">
                <ReturnCode authenticate="true" xmlns="urn:org:ebics:H005">000000</ReturnCode>
            </body>
        </ebicsKeyManagementResponse>',
            Version::V30,
        ];
    }

    /** @dataProvider certified */
    public function testOk(bool $certified, string $requestExpected, string $fakeReponse, string $version): void
    {
        $this->markTestIncomplete('orderdata not matching');
        $bank    = new Bank('myHostId', 'http://myurl.com', $certified, $version);
        $user    = new User('myPartId', 'myUserId');
        $keyRing = new KeyRing();
        $keyRing->setPassword('myPassword');

        $this->getSut($requestExpected, $fakeReponse, $version)->HIA($bank, $user, $keyRing, new DateTime('2010-10-10 10:10:10'));

        self::assertNotNull($keyRing->getUserCertificateE());
        self::assertNotNull($keyRing->getUserCertificateX());
    }
}
