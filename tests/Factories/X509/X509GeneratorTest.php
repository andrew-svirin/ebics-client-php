<?php

namespace EbicsApi\Ebics\Tests\Factories\X509;

use EbicsApi\Ebics\Factories\SignatureFactory;
use EbicsApi\Ebics\Models\Bank;
use EbicsApi\Ebics\Models\X509\BankX509Generator;
use EbicsApi\Ebics\Tests\AbstractEbicsTestCase;
use DateTime;

/**
 * Legacy X509 certificate generator @see X509GeneratorInterface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier, Andrew Svirin
 *
 * @group x509-generator
 */
class X509GeneratorTest extends AbstractEbicsTestCase
{

    /**
     * @group generate-bank-certificate-content
     */
    public function testGenerateBankCertificateContent()
    {
        $privateKey = $this->getPrivateKey();
        $publicKey = $this->getPublicKey();

        // Certificate generated for the 22/03/2020 (1 year validity)
        $generator = new BankX509Generator();
        $generator->setCertificateOptionsByBank(new Bank('H123456', 'https://test.bank.dom'));
        $generator->setX509StartDate(new DateTime('2020-03-21'));
        $generator->setX509EndDate(new DateTime('2021-03-22'));
        $generator->setSerialNumber('539453510852155194065233908413342789156542395956670254476154968597583055940');

        $signatureFactory = new SignatureFactory();
        $signature = $signatureFactory->createSignatureAFromKeys([
            'publickey' => $publicKey,
            'privatekey' => $privateKey,
        ], 'test123', $generator);

        self::assertEquals($signature->getPrivateKey(), $privateKey);
        self::assertEquals($signature->getPublicKey(), $publicKey);
        $this->assertCertificateEquals(
            $signature->getCertificateContent(),
            $this->getCertificateContent()
        );
    }

    /**
     * @param string $generatedContent
     * @param string $fileContent
     */
    private function assertCertificateEquals(string $generatedContent, string $fileContent)
    {
        $generatedInfos = openssl_x509_parse($generatedContent);
        $certificateInfos = openssl_x509_parse($fileContent);

        self::assertEquals($generatedInfos['subject'], $certificateInfos['subject']);
        self::assertEquals($generatedInfos['issuer'], $certificateInfos['issuer']);
        self::assertEquals(
            DateTime::createFromFormat(
                'U',
                $generatedInfos['validFrom_time_t']
            )->format('d/m/Y'),
            DateTime::createFromFormat('U', $certificateInfos['validFrom_time_t'])->format('d/m/Y')
        );
        self::assertEquals(
            DateTime::createFromFormat(
                'U',
                $generatedInfos['validTo_time_t']
            )->format('d/m/Y'),
            DateTime::createFromFormat(
                'U',
                $certificateInfos['validTo_time_t']
            )->format('d/m/Y'));
        self::assertEquals($generatedInfos['extensions'], $certificateInfos['extensions']);
    }

    /**
     * @return string
     */
    private function getCertificateContent()
    {
        return '-----BEGIN CERTIFICATE-----
MIICcDCCAdmgAwIBAgJLNTM5NDUzNTEwODUyMTU1MTk0MDY1MjMzOTA4NDEzMzQy
Nzg5MTU2NTQyMzk1OTU2NjcwMjU0NDc2MTU0OTY4NTk3NTgzMDU1OTQwMA0GCSqG
SIb3DQEBCwUAMBwxCzAJBgNVBAYMAkVVMQ0wCwYDVQQDDARCYW5rMB4XDTIwMDMy
MTAwMDAwMFoXDTIxMDMyMjAwMDAwMFowIjELMAkGA1UEBgwCRVUxEzARBgNVBAMM
CiouYmFuay5kb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAIzB7E84N4lz
CyS7IiMipDakOQjqTgcRWel8Y51zjH2MXRwVifil3An0x3PoaqCgcuNYfYPWsofW
MSw4VP2Sz5DIdG0ob+r2XIKvO4GjpxhNdTwyCL1RIz5nvQng1VIUo5s4LP/d4mvP
h8N73nkMQyqFz5WSZeXI452IrLs+LZtvAgMBAAGjcjBwMB0GA1UdDgQWBBREAqUl
0PKgjXu8AEk9bu5fsVn5NzAJBgNVHRMEAjAAMBMGA1UdJQQMMAoGCCsGAQUFBwME
MA4GA1UdDwEB/wQEAwIGQDAfBgNVHSMEGDAWgBREAqUl0PKgjXu8AEk9bu5fsVn5
NzANBgkqhkiG9w0BAQsFAAOBgQBsP7IphhIuI5ThodkmWGYBWntTaAUcTwjE8ahs
cJjN3WKFoc/4MD/bmbKGprfnDhsHjWVHhcNftbMAO54PSwewp4v0YtM4MOYp2DLJ
/r8u2KXJOjpIYCzk22IPWXtADArHHC1zyFgP2eEBM3ZjwN99M9gh49oMuwHai779
jjaKcA==
-----END CERTIFICATE-----';
    }

    /**
     * @return string
     */
    private function getPrivateKey()
    {
        return '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCMwexPODeJcwskuyIjIqQ2pDkI6k4HEVnpfGOdc4x9jF0cFYn4
pdwJ9Mdz6GqgoHLjWH2D1rKH1jEsOFT9ks+QyHRtKG/q9lyCrzuBo6cYTXU8Mgi9
USM+Z70J4NVSFKObOCz/3eJrz4fDe955DEMqhc+VkmXlyOOdiKy7Pi2bbwIDAQAB
AoGAMeWMn4iOJ2tgx+SOdWYSUExm64Ijpt2/wcUWivorE1Zuq0X3Yu1o0x6ylaQO
KGK4V19HHzU8lGqZg9N0TW99pI6Sp7IcOCakIm4RnyahAWzbKJzZ0XSAs1FHE/Gl
yRvDg+V1+Nx7i52jCbSbHSCB/EmoOlTaV+TJjtq8yFsNagECQQDKAUW5w4y9/w+K
ppWlyhBvV8zS1GztHQ8yJEcsTiHcUkyA3SF5KPATWw3c/lWN4uYw4XDTopdqWJNu
W+fwWdMNAkEAsmGhYqQlEI9r49Tz1anQAFtCUzBHEJtBWOuRa0C5BLJH6tyU2IK9
C1odvBbzlgLb1CzdjHal0/LYViHkrBa5awJBAL1uqAZmXUunLtnlEhzg+ryPZ6Km
VmedgqyQ3LWtp49HFjsaI9PNEiX0k3GUiIKAL0HTh8zPgpLV8ZviUAVTFtkCQHXU
G6BmwLzxn9i839vw8Z5qqaL9rtN/Wmj8IfBwrkY15V90GTXzFiCbhCysFHawqLi8
chPIg70/Gju646vwzsUCQGucnbDIXjnQK8nkzAiv/2+AluuCaP/DpBducbUhVWZZ
cTPigqsjIjo409hi01WNXMgZO3c6V7iAaaXtAmRmzVM=
-----END RSA PRIVATE KEY-----
';
    }

    /**
     * @return string
     */
    private function getPublicKey()
    {
        return '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCMwexPODeJcwskuyIjIqQ2pDkI
6k4HEVnpfGOdc4x9jF0cFYn4pdwJ9Mdz6GqgoHLjWH2D1rKH1jEsOFT9ks+QyHRt
KG/q9lyCrzuBo6cYTXU8Mgi9USM+Z70J4NVSFKObOCz/3eJrz4fDe955DEMqhc+V
kmXlyOOdiKy7Pi2bbwIDAQAB
-----END PUBLIC KEY-----
';
    }
}
