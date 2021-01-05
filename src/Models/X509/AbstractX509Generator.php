<?php

namespace AndrewSvirin\Ebics\Models\X509;

use AndrewSvirin\Ebics\Contracts\Crypt\RSAInterface;
use AndrewSvirin\Ebics\Contracts\Crypt\X509Interface;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Exceptions\X509\X509GeneratorException;
use AndrewSvirin\Ebics\Factories\Crypt\X509Factory;
use AndrewSvirin\Ebics\Services\X509\X509ExtensionOptionsNormalizer;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Default X509 certificate generator @see X509GeneratorInterface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier, Andrew Svirin
 */
abstract class AbstractX509Generator implements X509GeneratorInterface
{
    /** @var DateTimeInterface */
    protected $certificateStartDate;

    /** @var DateTimeInterface */
    protected $certificateEndDate;

    /** @var string */
    protected $serialNumber;

    /** @var X509Factory */
    private $x509Factory;

    public function __construct()
    {
        $this->x509Factory = new X509Factory();
        $this->certificateStartDate = (new DateTimeImmutable())->modify('-1 days');
        $this->certificateEndDate = (new DateTimeImmutable())->modify('+1 year');
        $this->serialNumber = $this->generateSerialNumber();
    }

    /**
     * Get certificate options
     *
     * @param array $options default generation options (may be empty)
     *
     * @return array the certificate options
     *
     * @see X509 options
     */
    abstract protected function getCertificateOptions(array $options = []): array;

    /**
     * @inheritDoc
     * @throws X509GeneratorException
     */
    public function generateX509(RSAInterface $privateKey, RSAInterface $publicKey, array $options = []): string
    {
        $options = array_merge([
            'subject' => [
                'domain' => null,
                'DN' => null,
            ],
            'issuer' => [
                'DN' => null, //Same as subject, means self-signed
            ],
            'extensions' => [],
        ], $this->getCertificateOptions($options));

        $subject = $this->generateSubject($publicKey, $options);
        $issuer = $this->generateIssuer($privateKey, $publicKey, $subject, $options);

        $x509 = $this->x509Factory->create();
        $x509->setStartDate($this->certificateStartDate->format('YmdHis'));
        $x509->setEndDate($this->certificateEndDate->format('YmdHis'));
        $x509->setSerialNumber($this->serialNumber);

        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');
        $x509->loadX509($result);

        foreach ($options['extensions'] as $id => $extension) {
            $extension = X509ExtensionOptionsNormalizer::normalize($extension);
            $setExtension = $x509->setExtension(
                $id,
                $extension['value'],
                $extension['critical'],
                $extension['replace']
            );
            if (false === $setExtension) {
                throw new X509GeneratorException(sprintf(
                    'Unable to set "%s" extension with value: %s',
                    $id,
                    var_export($extension['value'], true)
                ));
            }
        }

        $result = $x509->sign($issuer, $x509, 'sha256WithRSAEncryption');

        return $x509->saveX509($result);
    }

    /**
     * @param RSAInterface $publicKey
     * @param array $options
     *
     * @return X509Interface
     */
    protected function generateSubject(RSAInterface $publicKey, array $options): X509Interface
    {
        $subject = $this->x509Factory->create();
        $subject->setPublicKey($publicKey); // $pubKey is Crypt_RSA object

        if (!empty($options['subject']['DN'])) {
            $subject->setDN($options['subject']['DN']);
        }

        if (!empty($options['subject']['domain'])) {
            $subject->setDomain($options['subject']['domain']); // @phpstan-ignore-line
        }
        $subject->setKeyIdentifier($subject->computeKeyIdentifier($publicKey)); // id-ce-subjectKeyIdentifier

        return $subject;
    }

    /**
     * @param RSAInterface $privateKey
     * @param RSAInterface $publicKey
     * @param X509Interface $subject
     * @param array $options
     *
     * @return X509Interface
     */
    protected function generateIssuer(
        RSAInterface $privateKey,
        RSAInterface $publicKey,
        X509Interface $subject,
        array $options
    ): X509Interface {
        $issuer = $this->x509Factory->create();
        $issuer->setPrivateKey($privateKey); // $privKey is Crypt_RSA object

        if (!empty($options['issuer']['DN'])) {
            $issuer->setDN($options['issuer']['DN']);
        } else {
            $issuer->setDN($subject->getDN());
        }
        $issuer->setKeyIdentifier($subject->computeKeyIdentifier($publicKey));

        return $issuer;
    }

    /**
     * Generate 74 digits serial number represented in the string.
     *
     * @return string
     */
    protected function generateSerialNumber(): string
    {
        // prevent the first number from being 0
        $result = rand(1, 9);
        for ($i = 0; $i < 74; ++$i) {
            $result .= rand(0, 9);
        }

        return (string)$result;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCertificateStartDate(): DateTimeInterface
    {
        return $this->certificateStartDate;
    }

    public function setCertificateStartDate(DateTimeInterface $certificateStartDate): void
    {
        $this->certificateStartDate = $certificateStartDate;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCertificateEndDate(): DateTimeInterface
    {
        return $this->certificateEndDate;
    }

    public function setCertificateEndDate(DateTimeInterface $certificateEndDate): void
    {
        $this->certificateEndDate = $certificateEndDate;
    }

    /**
     * @return string
     */
    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    /**
     * @param string $serialNumber
     */
    public function setSerialNumber(string $serialNumber): void
    {
        $this->serialNumber = $serialNumber;
    }
}
