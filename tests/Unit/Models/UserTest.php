<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Models;

use AndrewSvirin\Ebics\Models\User;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass User
 */
class UserTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new User('hello', 'ehg!');

        self::assertSame('hello', $sUT->getPartnerId());
        self::assertSame('ehg!', $sUT->getUserId());
    }
}
