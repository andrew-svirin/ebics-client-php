<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models;

use AndrewSvirin\Ebics\Models\DOMDocument;
use PHPUnit\Framework\TestCase;

class DOMDocumentTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new DOMDocument();

        self::assertFalse($sUT->preserveWhiteSpace);
        self::assertSame("<?xml version='1.0' encoding='utf-8'?>", $sUT->getContent());
        self::assertFalse($sUT->formatOutput);
        self::assertSame('<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL, $sUT->getFormattedContent());
        self::assertTrue($sUT->formatOutput);
    }
}
