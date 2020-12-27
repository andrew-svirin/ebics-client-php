<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\Crypt\RSAInterface;
use AndrewSvirin\Ebics\Factories\Crypt\BigIntegerFactory;
use phpseclib\File\X509;

/**
 * Class CertificateX509 represents Certificate model in X509 structure.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @method RSAInterface getPublicKey()
 */
class CertificateX509 extends X509
{
    /**
     * Get Certificate serialNumber.
     *
     * @return string
     */
    public function getSerialNumber(): string
    {
        $certificateSerialNumber = BigIntegerFactory::createFromPhpSecLib(
            $this->currentCert['tbsCertificate']['serialNumber']
        );

        return $certificateSerialNumber->toString();
    }

    /**
     * Get Certificate Issuer DN property id-at-commonName.
     *
     * @return string
     */
    public function getInsurerName(): string
    {
        $certificateInsurerName = $this->getIssuerDNProp('id-at-commonName');

        return array_shift($certificateInsurerName);
    }
}
