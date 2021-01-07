<?php

namespace AndrewSvirin\Ebics\Tests\Factories\X509;

use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Models\X509\AbstractX509Generator;

/**
 * Legacy X509 certificate generator @see X509GeneratorInterface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class WeBankX509Generator extends AbstractX509Generator
{
    public function __construct()
    {
        parent::__construct();
        $this->setCertificateOptions([
            'subject' => [
                'DN' => [
                    'id-at-countryName' => 'FR',
                    'id-at-commonName' => '*.webank.fr',
                ],
            ],
            'issuer' => [
                'DN' => [
                    'id-at-countryName' => 'FR',
                    'id-at-commonName' => 'Webank Client',
                ],
            ],
        ]);
    }
}
