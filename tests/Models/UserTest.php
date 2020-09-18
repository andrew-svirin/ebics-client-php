<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Models;

use AndrewSvirin\Ebics\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new User(10, 12);

        self::assertSame('10', $sUT->getPartnerId());
        self::assertSame('12', $sUT->getUserId());

        $sUT = new User('hello', 'ehg!');

        self::assertSame('hello', $sUT->getPartnerId());
        self::assertSame('ehg!', $sUT->getUserId());
    }
}
