<?php

namespace AndrewSvirin\Ebics\Tests\Factories\X509;

use AndrewSvirin\Ebics\Models\X509\AbstractX509Generator;

/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class ZKBX509Generator extends AbstractX509Generator
{
    public function __construct()
    {
        parent::__construct();
        $this->setCertificateOptions([
            'subject' => [
                'DN' => [
                    'id-at-countryName' => 'DE',
                    'id-at-commonName' => '*.zkb.ch',
                ],
            ],
            'issuer' => [
                'DN' => [
                    'id-at-countryName' => 'DE',
                    'id-at-commonName' => 'ZKB Client',
                ],
            ],
        ]);
    }
}
