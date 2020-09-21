<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Factories\X509;

use AndrewSvirin\Ebics\Factories\CertificateFactory;
use AndrewSvirin\Ebics\Factories\X509\LegacyX509Generator;
use AndrewSvirin\Ebics\Factories\X509\X509GeneratorFactory;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function getcwd;
use function openssl_x509_parse;

/**
 * Legacy X509 certificate generator @see X509GeneratorInterface.
 */
class X509GeneratorTest extends TestCase
{
    public function testGenerateLegacyCertificateContent(): void
    {
        $privateKey = $this->getPrivateKey();
        $publicKey  = $this->getPublicKey();

        //Certificate generated the 22/03/2020 (1 year validity)
        X509GeneratorFactory::setGeneratorFunction(static function () {
            $generator = new LegacyX509Generator();
            $generator->setCertificateStartDate(new DateTime('2020-03-21'));
            $generator->setCertificateEndDate(new DateTime('2021-03-22'));
            $generator->setSerialNumber('539453510852155194065233908413342789156542395956670254476154968597583055940');

            return $generator;
        });

        $certificate = (new CertificateFactory())->generateCertificateAFromKeys([
            'publickey' => $publicKey,
            'privatekey' => $privateKey,
        ], true);

        $this->assertEquals($certificate->getPrivateKey(), $privateKey);
        $this->assertEquals($certificate->getPublicKey(), $publicKey);
        $this->assertCertificateEquals($certificate->getContent(), $this->getCertificateContent('legacy-signed.csr'));
    }

    public function testGenerateSilarhiCertificateContent(): void
    {
        $privateKey = $this->getPrivateKey();
        $publicKey  = $this->getPublicKey();

        //Certificate generated with https://certificatetools.com/ the 22/03/2020 (1 year validity)
        X509GeneratorFactory::setGeneratorFunction(static function () {
            $generator = new SilarhiX509Generator();
            $generator->setCertificateStartDate(new DateTime('2020-03-22'));
            $generator->setCertificateEndDate(new DateTime('2021-03-22'));
            $generator->setSerialNumber('37376365613564393736653364353135633333333932376336366134393663336133663135323432');

            return $generator;
        });

        $certificate = (new CertificateFactory())->generateCertificateAFromKeys([
            'publickey' => $publicKey,
            'privatekey' => $privateKey,
        ], true);

        $this->assertEquals($certificate->getPrivateKey(), $privateKey);
        $this->assertEquals($certificate->getPublicKey(), $publicKey);
        $this->assertCertificateEquals($certificate->getContent(), $this->getCertificateContent('silarhi-self-signed.csr'));
    }

    private function assertCertificateEquals(string $generatedContent, string $fileContent): void
    {
        $generatedInfos   = (array) openssl_x509_parse($generatedContent);
        $certificateInfos = (array) openssl_x509_parse($fileContent);

        $this->assertEquals($generatedInfos['subject'], $certificateInfos['subject']);
        $this->assertEquals($generatedInfos['issuer'], $certificateInfos['issuer']);
        $this->assertEquals(
            self::safeDate('U', (string) $generatedInfos['validFrom_time_t'])->format('d/m/Y'),
            self::safeDate('U', (string) $certificateInfos['validFrom_time_t'])->format('d/m/Y')
        );
        $this->assertEquals(
            self::safeDate('U', (string) $generatedInfos['validTo_time_t'])->format('d/m/Y'),
            self::safeDate('U', (string) $certificateInfos['validTo_time_t'])->format('d/m/Y')
        );
        $this->assertEquals($generatedInfos['extensions'], $certificateInfos['extensions']);
    }

    private static function safeDate(string $format, string $time): DateTime
    {
        $date = DateTime::createFromFormat($format, $time);

        if ($date === false) {
            throw new Exception('cant create date');
        }

        return $date;
    }

    private function getCertificateContent(string $name): string
    {
        return (string) file_get_contents(getcwd() . '/tests/_data/certificates/' . $name);
    }

    private function getPrivateKey(): string
    {
        return (string) file_get_contents(getcwd() . '/tests/_data/private_key.rsa');
    }

    private function getPublicKey(): string
    {
        return (string) file_get_contents(getcwd() . '/tests/_data/public_key.rsa');
    }
}
