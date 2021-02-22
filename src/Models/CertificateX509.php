<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Models\Crypt\X509;
use DateTime;

/**
 * Class CertificateX509 represents Certificate model in X509 structure.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
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
        $certificateSerialNumber = $this->currentCert['tbsCertificate']['serialNumber'];

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

    /**
     * Get validity start date.
     * @return DateTime
     */
    public function getValidityStartDate(): DateTime
    {
        $notBefore = $this->currentCert['tbsCertificate']['validity']['notBefore']['utcTime'];
        $notBeforeTimestamp = strtotime($notBefore);

        $startDate = new DateTime();
        $startDate->setTimestamp($notBeforeTimestamp);

        return $startDate;
    }
}
