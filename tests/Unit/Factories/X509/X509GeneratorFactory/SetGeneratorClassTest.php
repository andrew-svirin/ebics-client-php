<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Factories\X509\X509GeneratorFactory;

use AndrewSvirin\Ebics\Factories\X509\LegacyX509Generator;
use AndrewSvirin\Ebics\Factories\X509\X509GeneratorFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SetGeneratorClassTest extends TestCase
{
    public function testFail(): void
    {
        X509GeneratorFactory::setGeneratorFunction(null);

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('The class "AndrewSvirin\Ebics\Factories\X509\X509GeneratorFactory" must implements AndrewSvirin\Ebics\Contracts\X509GeneratorInterface');

        X509GeneratorFactory::setGeneratorClass(X509GeneratorFactory::class);
    }

    public function testOk(): void
    {
        X509GeneratorFactory::setGeneratorFunction(null);
        X509GeneratorFactory::setGeneratorClass(LegacyX509Generator::class);
        self::assertInstanceOf(LegacyX509Generator::class, X509GeneratorFactory::create([]));
    }
}
