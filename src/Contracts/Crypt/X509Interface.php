<?php

namespace EbicsApi\Ebics\Contracts\Crypt;

use EbicsApi\Ebics\Models\Crypt\X509;

/**
 * Crypt X509 representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface X509Interface
{

    /**
     * Set certificate start date.
     *
     * @param string $date
     *
     * @return void
     */
    public function setStartDate(string $date);

    /**
     * Set certificate end date.
     *
     * @param string $date
     *
     * @return void
     */
    public function setEndDate(string $date);

    /**
     * Set Serial Number.
     *
     * @param string $serial
     * @param int $base optional
     *
     * @return void
     */
    public function setSerialNumber(string $serial, int $base = -256);

    /**
     * Sign an X.509 certificate.
     *
     * $issuer's private key needs to be loaded.
     * $subject can be either an existing X.509 cert (if you want to resign it),
     * a CSR or something with the DN and public key explicitly set.
     *
     * @param X509Interface $issuer
     * @param X509Interface $subject
     * @param string $signatureAlgorithm optional
     *
     * @return mixed
     */
    public function sign(
        X509Interface $issuer,
        X509Interface $subject,
        string $signatureAlgorithm = 'sha1WithRSAEncryption'
    );

    /**
     * Load X.509 certificate.
     *
     * Returns an associative array describing the X.509 cert or a false if the cert failed to load.
     *
     * @param string|false $cert
     *
     * @return mixed
     */
    public function loadX509($cert);

    /**
     * Set a certificate, CSR or CRL Extension.
     *
     * @param string $id
     * @param mixed $value
     * @param bool $critical optional
     * @param bool $replace optional
     * @param string|null $path optional
     *
     * @return bool
     */
    public function setExtension(
        string $id,
        $value,
        bool $critical = false,
        bool $replace = true,
        string $path = null
    );

    /**
     * Save X.509 certificate.
     *
     * @param array|false $cert
     *
     * @return string|false
     */
    public function saveX509($cert);

    /**
     * Set public key.
     *
     * @param RSAInterface $key
     *
     * @return void
     */
    public function setPublicKey(RSAInterface $key);

    /**
     * Gets the public key.
     *
     * @return RSAInterface|null
     */
    public function getPublicKey(): ?RSAInterface;

    /**
     * Set private key.
     *
     * @param RSAInterface $key
     *
     * @return void
     */
    public function setPrivateKey(RSAInterface $key);

    /**
     * Returns the private key.
     *
     * The private key is only returned if the currently loaded key contains the constituent prime numbers.
     *
     * @return RSAInterface|null
     */
    public function getPrivateKey(): ?RSAInterface;

    /**
     * Set a Distinguished Name.
     *
     * @param mixed $dn
     * @param string $type optional
     *
     * @return bool
     */
    public function setDN($dn, string $type = 'utf8String');

    /**
     * Get the Distinguished Name for a certificates subject.
     *
     * @return mixed
     */
    public function getDN();

    /**
     * Set the domain name's which the cert is to be valid for.
     *
     * @return void
     */
    public function setDomain();

    /**
     * Sets the subject key identifier
     *
     * This is used by the id-ce-authorityKeyIdentifier and the id-ce-subjectKeyIdentifier extensions.
     *
     * @param string $value
     *
     * @return void
     */
    public function setKeyIdentifier(string $value);

    /**
     * Compute a public key identifier.
     *
     * Although key identifiers may be set to any unique value, this function
     * computes key identifiers from public key according to the two
     * recommended methods (4.2.1.2 RFC 3280).
     *
     * @param mixed $key optional
     *
     * @return string binary key identifier
     */
    public function computeKeyIdentifier($key = null);

    /**
     * Format a public key as appropriate.
     *
     * @return array|null
     */
    public function formatSubjectPublicKey(): ?array;

    /**
     * Save current cert in X509.
     *
     * @return string|false
     */
    public function saveX509CurrentCert();

    /**
     * Get an individual Distinguished Name property for a certificate/crl issuer.
     *
     * @param string $propName
     * @param bool $withType optional
     *
     * @return mixed
     */
    public function getIssuerDNProp(string $propName, bool $withType = false);

    /**
     * Get a CSR attribute.
     *
     * Returns the attribute if it exists and false if not
     *
     * @param string $id
     * @param int $disposition optional
     * @param array|null $csr optional
     *
     * @return mixed
     */
    public function getAttribute(string $id, int $disposition = X509::ATTR_ALL, array $csr = null);
}
