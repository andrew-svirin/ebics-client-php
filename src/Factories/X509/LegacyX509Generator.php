<?php

namespace AndrewSvirin\Ebics\Factories\X509;

use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;

/**
 * Legacy X509 certificate generator @see X509GeneratorInterface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class LegacyX509Generator extends AbstractX509Generator
{
    protected function getCertificateOptions(array $options = []): array
    {
        return [
            'subject' => [
                'DN' => [
                    'id-at-countryName' => 'FR',
                    'id-at-stateOrProvinceName' => 'Seine-et-Marne',
                    'id-at-localityName' => 'Melun',
                    'id-at-organizationName' => 'Elcimai Informatique',
                    'id-at-commonName' => '*.webank.fr',
                ]
            ],
            'issuer' => [
                'DN' => [
                    'id-at-countryName' => 'US',
                    'id-at-organizationName' => 'GeoTrust Inc.',
                    'id-at-commonName' => 'GeoTrust SSL CA - G3',
                ]
            ],
            'extensions' => [
                'id-ce-subjectAltName' => [
                    'value' => [
                        [
                            'dNSName' => '*.webank.fr',
                        ],
                        [
                            'dNSName' => 'webank.fr',
                        ],
                    ]
                ],
                'id-ce-basicConstraints' => [
                    'value' => [
                        'cA' => false,
                    ]
                ],
                'id-ce-keyUsage' => [
                    'value' => ['keyEncipherment', 'digitalSignature'],
                    'critical' => true
                ],
                'id-ce-cRLDistributionPoints' => [
                    [
                        'distributionPoint' =>
                            [
                                'fullName' =>
                                    [
                                        [
                                            'uniformResourceIdentifier' => 'http://gn.symcb.com/gn.crl',
                                        ],
                                    ],
                            ],
                    ]
                ],
                'id-ce-certificatePolicies' => [
                    [
                        'policyIdentifier' => '2.23.140.1.2.2',
                        'policyQualifiers' =>
                            [
                                [
                                    'policyQualifierId' => 'id-qt-cps',
                                    'qualifier' =>
                                        [
                                            'ia5String' => 'https://www.geotrust.com/resources/repository/legal',
                                        ],
                                ],
                                [
                                    'policyQualifierId' => 'id-qt-unotice',
                                    'qualifier' =>
                                        [
                                            'explicitText' =>
                                                [
                                                    'utf8String' => 'https://www.geotrust.com/resources/repository/legal',
                                                ],
                                        ],
                                ],
                            ],
                    ],
                ],
                'id-ce-extKeyUsage' => [
                    'value' => ['id-kp-serverAuth', 'id-kp-clientAuth']
                ],
                'id-pe-authorityInfoAccess' => [
                    [
                        'accessMethod' => 'id-ad-ocsp',
                        'accessLocation' =>
                            [
                                'uniformResourceIdentifier' => 'http://gn.symcd.com',
                            ],
                    ],
                    [
                        'accessMethod' => 'id-ad-caIssuers',
                        'accessLocation' =>
                            [
                                'uniformResourceIdentifier' => 'http://gn.symcb.com/gn.crt',
                            ],
                    ],
                ],
                '1.3.6.1.4.1.11129.2.4.2' => 'BIIBbAFqAHcA3esdK3oNT6Ygi4GtgWhwfi6OnQHVXIiNPRHEzbbsvswAAAFdCJcynQAABAMASDBGAiEAgJgQE9466xkMy6olq+1xvTGt9ROXcgmdUIht4EE4g14CIQDZNjYcKbVU6taN/unn2WHlsDgphMgQXzALHt7vrI/bIgB2AKS5CZC0GFgUh7sTosxncAo8NZgE+RvfuON3zQ7IDdwQAAABXQiXMtAAAAQDAEcwRQIgTx+2uvI9ReTYiO9Ii85qoet1dc+y58RT4wAO9C4OCakCIQCRhO2kJWxeSfP1L2/Q24I3MGLMn//mwhdJ43mu4e9n8gB3AO5Lvbd1zmC64UJpH6vhnmajD35fsHLYgwDEe4l6qP3LAAABXQiXNJcAAAQDAEgwRgIhAM+dK3OLBL5nGzp/PSt3yRab85AD3jz69g5TqGdrMuhkAiEAnDMu/ZiqyBWO3+li3L9/hi3BcHX74rAmA3OX1jNxIKE='
            ]
        ];
    }
}