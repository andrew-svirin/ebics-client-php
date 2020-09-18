<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Factories\X509;

use AndrewSvirin\Ebics\Factories\X509\AbstractX509Generator;

/**
 * Legacy X509 certificate generator @see X509GeneratorInterface.
 */
class SilarhiX509Generator extends AbstractX509Generator
{
    protected function getCertificateOptions(array $options = []): array
    {
        return [
            'subject' => [
                'domain' => 'silarhi.fr',
                'DN' => [
                    'id-at-countryName' => 'FR',
                    'id-at-stateOrProvinceName' => 'Occitanie',
                    'id-at-localityName' => 'Toulouse',
                    'id-at-organizationName' => 'SILARHI',
                    'id-at-commonName' => 'silarhi.fr',
                ],
            ],
            'extensions' => [
                'id-ce-subjectAltName' => [
                    'value' => [
                        ['dNSName' => '*.silarhi.fr'],
                    ],
                ],
                'id-ce-basicConstraints' => [
                    'value' => ['CA' => false],
                ],
                'id-ce-keyUsage' => [
                    'value' => ['keyEncipherment', 'digitalSignature', 'nonRepudiation'],
                    'critical' => true,
                ],
                'id-ce-extKeyUsage' => [
                    'value' => ['id-kp-serverAuth', 'id-kp-clientAuth'],
                ],
            ],
        ];
    }
}
