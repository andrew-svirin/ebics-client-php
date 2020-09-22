<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\ResponseHandler;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Response;
use PHPUnit\Framework\TestCase;

use function base64_encode;

/**
 * @coversDefaultClass ResponseHandler
 */
class RetrieveOrderDataTest extends TestCase
{
    public function testReturnBody(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <elem>
            <body xmlns="urn:org:ebics:H004">
                <DataTransfer xmlns="urn:org:ebics:H004">
                    <OrderData xmlns="urn:org:ebics:H004">hi!</OrderData>
                    <DataEncryptionInfo xmlns="urn:org:ebics:H004">
                        <TransactionKey xmlns="urn:org:ebics:H004">' . base64_encode('hi!') . '</TransactionKey>
                    </DataEncryptionInfo>
                </DataTransfer>
            </body>
        </elem>
';

        $actual = $sUT->retrieveOrderData(new Response($xml));

        self::assertEquals('hi!', $actual->getOrderData());
        self::assertEquals('hi!', $actual->getTransactionKey());
    }

    public function testNoOrderData(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <elem>
            <body xmlns="urn:org:ebics:H004">
                <DataTransfer xmlns="urn:org:ebics:H004">
                    <DataEncryptionInfo xmlns="urn:org:ebics:H004">
                        <TransactionKey xmlns="urn:org:ebics:H004">' . base64_encode('hi!') . '</TransactionKey>
                    </DataEncryptionInfo>
                </DataTransfer>
            </body>
        </elem>
';

        self::expectException(EbicsException::class);
        $sUT->retrieveOrderData(new Response($xml));
    }

    public function testNoTransactionKey(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <elem>
            <body xmlns="urn:org:ebics:H004">
                <DataTransfer xmlns="urn:org:ebics:H004">
                    <OrderData xmlns="urn:org:ebics:H004">hi!</OrderData>
                    <DataEncryptionInfo xmlns="urn:org:ebics:H004">
                    </DataEncryptionInfo>
                </DataTransfer>
            </body>
        </elem>
';

        self::expectException(EbicsException::class);
        $sUT->retrieveOrderData(new Response($xml));
    }
}
