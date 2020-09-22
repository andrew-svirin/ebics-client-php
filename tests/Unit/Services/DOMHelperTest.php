<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Services;

use AndrewSvirin\Ebics\Models\DOMDocument;
use AndrewSvirin\Ebics\Services\DOMHelper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass DOMHelper
 */
class DOMHelperTest extends TestCase
{
    public function testFailWrongParams(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('empty set');

        DOMHelper::safeItemValue(false);
    }

    public function testFailCauseEmpty(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('index 0 is null');

        $document = new DOMDocument();
        DOMHelper::safeItemValue($document->getElementsbytagname('user'));
    }
}
