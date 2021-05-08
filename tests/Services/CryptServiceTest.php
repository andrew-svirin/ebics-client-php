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
     * @group crypt-services-generate-keys
     */
    public function testGenerateKeys()
    {
        $credentialsId = 2;
        $client = $this->setupClient($credentialsId);
        $cryptService = new CryptService();

        $keys = $cryptService->generateKeys($client->getKeyRing()->getPassword());

        self::assertArrayHasKey('privatekey', $keys);
        self::assertArrayHasKey('publickey', $keys);
        self::assertArrayHasKey('partialkey', $keys);
    }
}
