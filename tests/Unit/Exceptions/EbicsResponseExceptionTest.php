<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Exceptions;

use AndrewSvirin\Ebics\Exceptions\EbicsResponseException;
use PHPUnit\Framework\TestCase;

class EbicsResponseExceptionTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new EbicsResponseException('10', null);

        self::assertSame('', $sUT->getMessage());
        self::assertSame(10, $sUT->getCode());

        $sUT = new EbicsResponseException('10', 't');

        self::assertSame('t', $sUT->getMessage());
        self::assertSame(10, $sUT->getCode());
    }
}
