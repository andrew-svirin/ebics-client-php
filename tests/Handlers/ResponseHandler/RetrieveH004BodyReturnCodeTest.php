<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Handlers\ResponseHandler;

use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\OrderData;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * coversDefaultClass ResponseHandler
 */
class RetrieveH004BodyReturnCodeTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <body xmlns="urn:org:ebics:H004">
            <ReturnCode xmlns="urn:org:ebics:H004">hi!</ReturnCode>
        </body>
';

        self::assertEquals('hi!', $sUT->retrieveH004BodyReturnCode(new OrderData($xml)));
    }

    public function testNotFound(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H004">
        </header>
';

        self::expectException(RuntimeException::class);
        $sUT->retrieveH004BodyReturnCode(new OrderData($xml));
    }
}
