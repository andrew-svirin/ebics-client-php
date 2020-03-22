<?php

namespace AndrewSvirin\Ebics\Factories\X509;

use AndrewSvirin\Ebics\Contracts\X509GeneratorFactoryInterface;
use phpseclib\File\X509;

/**
 * X509 extensions options normalizer.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class X509ExtentionOptionsNormalizer
{
    /**
     * @param mixed $options
     * @see X509::setExtension()
     */
    public static function normalize($options)
    {
        $value = null;
        $critical = false;
        $replace = true;

        if (!is_array($options)) {
            $value = $options;
        } else {
            if (!isset($options['value'])) {
                $value = $options;
            } else {
                $value = $options['value'];
                if (isset($options['critical'])) {
                    $critical = $options['critical'];
                }
                if (isset($options['replace'])) {
                    $replace = $options['replace'];
                }
            }
        }

        return [
            'value' => $value,
            'critical' => $critical,
            'replace' => $replace,
        ];
    }
}