<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Services\CryptService;

use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

use function bin2hex;

/**
 * @coversDefaultClass CryptService
 */
class CalculateDigestTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new CryptService();

        $certificat = new Certificate('test', '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWidncNpkqmHnFZbicgeZfmRht
/+TzVO9RtZQ7NDHPWvWYih3LBMsBKfX9rSKeso+c+feDLge5+Tp9vKt3Ip1vnaBr
48jfAvkmzQyGk6OAMk2HTXY7rOZls3Cv5jhuR95h+pO6AVCloN6wq4+Y5PnyyX7Z
A3jkP/yhA0WITVryywIDAQAB
-----END PUBLIC KEY-----');

        self::assertSame('c50478b479b9cba341737a378448596c237d14933c4f2830fc4bc7a73168ee47', bin2hex($sUT->calculateDigest($certificat)));
    }
}
