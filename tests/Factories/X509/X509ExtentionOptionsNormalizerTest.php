<?php


namespace AndrewSvirin\Ebics\Tests\Factories\X509;

use AndrewSvirin\Ebics\Factories\X509\X509ExtentionOptionsNormalizer;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;

class X509ExtentionOptionsNormalizerTest extends AbstractEbicsTestCase
{
    /**
     * @dataProvider getOptions
     */
    public function testOptions($value, $expected)
    {
        $actualValue = X509ExtentionOptionsNormalizer::normalize($value);

        $this->assertEquals($expected, $actualValue);
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
            [['value' => ['foo'], 'critical' => false, 'replace' => false], ['value' => ['foo'], 'critical' => false, 'replace' => false]],
            [['value' => ['foo'], 'critical' => true, 'replace' => true], ['value' => ['foo'], 'critical' => true, 'replace' => true]],
        ];
    }
}