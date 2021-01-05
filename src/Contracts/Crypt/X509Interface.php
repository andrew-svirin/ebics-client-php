<?php

namespace AndrewSvirin\Ebics\Contracts\Crypt;

use phpseclib\File\X509;

/**
 * Crypt X509 representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface X509Interface
{

    /**
     * @param string $date
     *
     * @return void
     *
     * @see \phpseclib\File\X509::setStartDate()
     */
    public function setStartDate($date);

    /**
     * @param string $date
     *
     * @return void
     * @see \phpseclib\File\X509::setEndDate()
     *
     */
    public function setEndDate($date);

    /**
     * @param string $serial
     * @param int $base optional
     *
     * @return void
     *
     * @@see \phpseclib\File\X509::setSerialNumber()
     */
    public function setSerialNumber($serial, $base = -256);

    /**
     * @param X509Interface $issuer
     * @param X509Interface $subject
     * @param string $signatureAlgorithm optional
     *
     * @return mixed
     *
     * @see \phpseclib\File\X509::sign()
     */
    public function sign($issuer, $subject, $signatureAlgorithm = 'sha1WithRSAEncryption');

    /**
     * @param string $cert
     * @param int $mode
     *
     * @return mixed
     *
     * @see \phpseclib\File\X509::loadX509()
     */
    public function loadX509($cert, $mode = X509::FORMAT_AUTO_DETECT);

    /**
     * @param string $id
     * @param mixed $value
     * @param bool $critical optional
     * @param bool $replace optional
     *
     * @return bool
     *
     * @see \phpseclib\File\X509::setExtension()
     */
    public function setExtension($id, $value, $critical = false, $replace = true);

    /**
     * @param array $cert
     * @param int $format optional
     *
     * @return string
     *
     * @see \phpseclib\File\X509::saveX509()
     */
    public function saveX509($cert, $format = X509::FORMAT_PEM);

    /**
     * @param object $key
     *
     * @return bool
     *
     * @see \phpseclib\File\X509::setPublicKey()
     */
    public function setPublicKey($key);

    /**
     * @param object $key
     *
     * @return void
     *
     * @see \phpseclib\File\X509::setPrivateKey()
     */
    public function setPrivateKey($key);

    /**
     * @param mixed $dn
     * @param bool $merge optional
     * @param string $type optional
     *
     * @return bool
     *
     * @see \phpseclib\File\X509::setDN()
     */
    public function setDN($dn, $merge = false, $type = 'utf8String');

    /**
     * @param mixed $format optional
     * @param array $dn optional
     *
     * @return bool
     *
     * @see \phpseclib\File\X509::getDN()
     */
    public function getDN($format = X509::DN_ARRAY, $dn = null);

    /**
     * @return void
     *
     * @see \phpseclib\File\X509::setDomain()
     */
    public function setDomain();

    /**
     * @param string $value
     *
     * @return void
     *
     * @see \phpseclib\File\X509::setKeyIdentifier()
     */
    public function setKeyIdentifier($value);

    /**
     * @param mixed $key optional
     * @param int $method optional
     *
     * @return string binary key identifier
     *
     * @see \phpseclib\File\X509::computeKeyIdentifier()
     */
    public function computeKeyIdentifier($key = null, $method = 1);
}
