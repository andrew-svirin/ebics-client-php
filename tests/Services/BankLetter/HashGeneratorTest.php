<?php

namespace AndrewSvirin\Ebics\Tests\Services\BankLetter;

use AndrewSvirin\Ebics\Factories\SignatureFactory;
use AndrewSvirin\Ebics\Services\BankLetter\HashGenerator\CertificateHashGenerator;
use AndrewSvirin\Ebics\Services\BankLetter\HashGenerator\PublicKeyHashGenerator;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;
use AndrewSvirin\Ebics\Tests\Factories\X509\WeBankX509Generator;
use DateTime;

/**
 * Class HashGeneratorTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group hash-generator
 */
class HashGeneratorTest extends AbstractEbicsTestCase
{

    /**
     * @group hash-generator-certificate
     * @covers
     */
    public function testGenerateCertificateHash()
    {
        $hashGenerator = new CertificateHashGenerator();

        $privateKey = $this->getPrivateKey();
        $publicKey = $this->getPublicKey();

        //Certificate generated with https://certificatetools.com/ the 22/03/2020 (1 year validity)
        $certificateGenerator = new WeBankX509Generator();
        $certificateGenerator->setX509StartDate(new DateTime('2020-03-22'));
        $certificateGenerator->setX509EndDate(new DateTime('2021-03-22'));
        $certificateGenerator->setSerialNumber('37376365613564393736653364353135633333333932376336366134393663336133663135323432');

        $certificateFactory = new SignatureFactory();

        $signature = $certificateFactory->createSignatureAFromKeys([
            'publickey' => $publicKey,
            'privatekey' => $privateKey,
        ], 'test123', $certificateGenerator);

        $hash = $hashGenerator->generate($signature);

        $this->assertEquals('fc3f5d1340438d9603697be274c6f807e4faa5b6a566cf56b4651bde9159ae80', $hash);
    }

    /**
     * @group hash-generator-public-key
     * @covers
     */
    public function testGeneratePublicKeyHash()
    {
        $hashGenerator = new PublicKeyHashGenerator();

        $privateKey = $this->getPrivateKey();
        $publicKey = $this->getPublicKey();

        $certificateFactory = new SignatureFactory();

        $signature = $certificateFactory->createSignatureAFromKeys([
            'publickey' => $publicKey,
            'privatekey' => $privateKey,
        ], 'test123');

        $hash = $hashGenerator->generate($signature);

        $this->assertEquals('e1955c3873327e1791aca42e350cea48196f7934648d48b60228eaf5d10ee0c4', $hash);
    }

    /**
     * @return string
     */
    private function getPrivateKey()
    {
        return file_get_contents($this->data . '/private_key.rsa');
    }

    /**
     * @return string
     */
    private function getPublicKey()
    {
        return file_get_contents($this->data . '/public_key.rsa');
    }
}
