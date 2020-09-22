<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Handlers\ResponseHandler;

use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass ResponseHandler
 */
class RetrieveH000ReturnCodeTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <SystemReturnCode xmlns="http://www.ebics.org/H000">
            <ReturnCode xmlns="http://www.ebics.org/H000">hi!</ReturnCode>
        </SystemReturnCode>
';

        self::assertEquals('hi!', $sUT->retrieveH000ReturnCode(new Response($xml)));
    }

    public function testNotFound(): void
    {
        $sUT = new ResponseHandler();

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <header xmlns="urn:org:ebics:H000">
        </header>
';

        self::expectException(RuntimeException::class);
        $sUT->retrieveH000ReturnCode(new Response($xml));
    }
}
