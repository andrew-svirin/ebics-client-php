<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Factories\X509;

use AndrewSvirin\Ebics\Factories\X509\X509ExtensionOptionsNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Legacy X509 certificate generator @see X509GeneratorInterface.
 */
class X509ExtensionOptionsNormalizerTest extends TestCase
{
    /**
     * @param string|array $value
     *
     * @dataProvider getOptions
     */
    public function testOptions($value, array $expected): void
    {
        $actualValue = X509ExtensionOptionsNormalizer::normalize($value);

        $this->assertEquals($expected, $actualValue);
    }

    public function getOptions(): array
    {
        return [
            ['foo', ['value' => 'foo', 'critical' => false, 'replace' => true]],
            [['value' => 'foo'], ['value' => 'foo', 'critical' => false, 'replace' => true]],
            [['foo'], ['value' => ['foo'], 'critical' => false, 'replace' => true]],
            [['value' => ['foo']], ['value' => ['foo'], 'critical' => false, 'replace' => true]],
            [['value' => ['foo'], 'critical' => false], ['value' => ['foo'], 'critical' => false, 'replace' => true]],
            [['value' => ['foo'], 'critical' => true], ['value' => ['foo'], 'critical' => true, 'replace' => true]],
            [['value' => ['foo'], 'critical' => false, 'replace' => false], ['value' => ['foo'], 'critical' => false, 'replace' => false]],
            [['value' => ['foo'], 'critical' => true, 'replace' => true], ['value' => ['foo'], 'critical' => true, 'replace' => true]],
        ];
    }
}
