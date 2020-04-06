<?php

namespace AndrewSvirin\Ebics\Factories\X509;

use phpseclib\File\X509;

/**
 * X509 extensions options normalizer.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class X509ExtensionOptionsNormalizer
{
    /**
     * @param mixed|string|array $options
     *
     * @return array
     *
     * @see X509::setExtension()
     */
    public static function normalize($options): array
    {
        $value = null;
        $critical = false;
        $replace = true;

        if (!\is_array($options)) {
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
