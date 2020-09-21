<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Factories\X509\X509GeneratorFactory;

use AndrewSvirin\Ebics\Factories\X509\X509GeneratorFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class CreateTest extends TestCase
{
    public function testFailCauseReturnNull(): void
    {
        X509GeneratorFactory::setGeneratorFunction(static function () {
            return null;
        });

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('The X509GeneratorFactory::generatorFunction must returns a instance of "AndrewSvirin\Ebics\Contracts\X509GeneratorInterface", none returned');

        X509GeneratorFactory::create([]);
    }

    public function testFailCauseReturnWrongClass(): void
    {
        X509GeneratorFactory::setGeneratorFunction(static function () {
            return new stdClass();
        });

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('The class "stdClass" must implements AndrewSvirin\Ebics\Contracts\X509GeneratorInterface');

        X509GeneratorFactory::create([]);
    }
}
