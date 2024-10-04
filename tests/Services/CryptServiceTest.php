<?php

namespace AndrewSvirin\Ebics\Tests\Services;

use AndrewSvirin\Ebics\Services\CryptService;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;

/**
 * Class CryptServiceTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group crypt-services
 */
class CryptServiceTest extends AbstractEbicsTestCase
{

    /**
     * @group crypt-service-generate-keys
     */
    public function testGenerateKeys()
    {
        $credentialsId = 2;
        $client = $this->setupClientV25($credentialsId);
        $cryptService = new CryptService();

        $keys = $cryptService->generateKeys($client->getKeyring()->getPassword());

        self::assertArrayHasKey('privatekey', $keys);
        self::assertArrayHasKey('publickey', $keys);
        self::assertArrayHasKey('partialkey', $keys);
    }

    /**
     * @group crypt-service-check-password
     */
    public function testCheckPassword()
    {
        $credentialsId = 2;
        $client = $this->setupClientV25($credentialsId);
        $cryptService = new CryptService();

        $keyring = $client->getKeyring();

        $this->assertTrue($cryptService->checkKeyring($keyring));

        $keyring->setPassword('incorrect_password');

        $this->assertFalse($cryptService->checkKeyring($keyring));
    }
}
