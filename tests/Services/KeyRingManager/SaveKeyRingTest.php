<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Services\KeyRingManager;

use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Services\KeyRingManager;
use PHPUnit\Framework\TestCase;

use function unlink;

class SaveKeyRingTest extends TestCase
{
    public function testNonExistingFile(): void
    {
        @unlink(__DIR__ . '/mustexist');

        $sUT     = new KeyRingManager(__DIR__ . '/mustexist', 'test');
        $keyRing = (new KeyRing());
        $keyRing->setPassword('test');

        $sUT->saveKeyRing($keyRing);

        self::assertFileExists(__DIR__ . '/mustexist');
    }
}
