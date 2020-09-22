<?php

namespace AndrewSvirin\Ebics\Models;

use phpseclib\Crypt\RSA;
use phpseclib\File\X509;
use phpseclib\Math\BigInteger;

/**
 * Class CertificateX509 represents Certificate model in X509 structure.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @method RSA getPublicKey()
 */
class CertificateX509 extends X509
{
    public function __construct(string $content)
    {
        parent::__construct();

        $this->loadX509($content);
    }

    /**
     * Get Certificate serialNumber.
     */
    public function getSerialNumber(): string
    {
        /* @var $certificateSerialNumber BigInteger */
        $certificateSerialNumber = $this->currentCert['tbsCertificate']['serialNumber'];

        return $certificateSerialNumber->toString();
    }

    /**
     * Get Certificate Issuer DN property id-at-commonName.
     */
    public function getInsurerName(): string
    {
        $certificateInsurerName = $this->getIssuerDNProp('id-at-commonName');

        return array_shift($certificateInsurerName);
    }
}
