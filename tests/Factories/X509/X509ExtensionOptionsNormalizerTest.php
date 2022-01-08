<?php

namespace AndrewSvirin\Ebics\Tests\Factories\X509;

use AndrewSvirin\Ebics\Services\X509\X509ExtensionOptionsNormalizer;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;

/**
 * Legacy X509 certificate generator @see X509GeneratorInterface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class X509ExtensionOptionsNormalizerTest extends AbstractEbicsTestCase
{
    /**
     * @dataProvider getOptions
     *
     * @param $value
     * @param $expected
     */
    public function testOptions($value, $expected)
    {
        $actualValue = X509ExtensionOptionsNormalizer::normalize($value);

        self::assertEquals($expected, $actualValue);
    }

    public function getOptions()
    {
        return [
            ['foo', ['value' => 'foo', 'critical' => false, 'replace' => true]],
            [['value' => 'foo'], ['value' => 'foo', 'critical' => false, 'replace' => true]],
            [['foo'], ['value' => ['foo'], 'critical' => false, 'replace' => true]],
            [['value' => ['foo']], ['value' => ['foo'], 'critical' => false, 'replace' => true]],
            [['value' => ['foo'], 'critical' => false], ['value' => ['foo'], 'critical' => false, 'replace' => true]],
            [['value' => ['foo'], 'critical' => true], ['value' => ['foo'], 'critical' => true, 'replace' => true]],
            [
                ['value' => ['foo'], 'critical' => false, 'replace' => false],
                ['value' => ['foo'], 'critical' => false, 'replace' => false]
            ],
            [
                ['value' => ['foo'], 'critical' => true, 'replace' => true],
                ['value' => ['foo'], 'critical' => true, 'replace' => true]
            ],
        ];
    }
}
