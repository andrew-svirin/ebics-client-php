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

   /**
    * Get Certificate serialNumber.
    * @return string
    */
   public function getSerialNumber(): string
   {
      /* @var $certificateSerialNumber BigInteger */
      $certificateSerialNumber = $this->currentCert['tbsCertificate']['serialNumber'];
      $certificateSerialNumberValue = $certificateSerialNumber->toString();
      return $certificateSerialNumberValue;
   }

   /**
    * Get Certificate Issuer DN property id-at-commonName.
    * @return string
    */
   public function getInsurerName(): string
   {
      $certificateInsurerName = $this->getIssuerDNProp('id-at-commonName');
      $certificateInsurerNameValue = array_shift($certificateInsurerName);
      return $certificateInsurerNameValue;
   }

}