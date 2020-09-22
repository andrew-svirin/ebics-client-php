<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Factories\KeyRingFactory;

use AndrewSvirin\Ebics\Factories\KeyRingFactory;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use PHPUnit\Framework\TestCase;

use function base64_decode;

/**
 * @coversDefaultClass KeyRingFactory
 */
class BuildKeyRingFromDataTest extends TestCase
{
    public function testOkEmpty(): void
    {
        $keyRing = new KeyRing();

        self::assertEquals($keyRing, KeyRingFactory::buildKeyRingFromData([]));
    }

    public function testOkWithData(): void
    {
        $keyRing = new KeyRing();
        $keyRing->setUserCertificateA(new Certificate(Certificate::TYPE_A, base64_decode('pub_user_a'), base64_decode('priv_user_a'), base64_decode('content_user_a')));
        $keyRing->setUserCertificateE(new Certificate(Certificate::TYPE_E, base64_decode('pub_user_e'), base64_decode('priv_user_e'), base64_decode('content_user_e')));
        $keyRing->setUserCertificateX(new Certificate(Certificate::TYPE_X, base64_decode('pub_user_x'), base64_decode('priv_user_x'), base64_decode('content_user_x')));
        $keyRing->setBankCertificateE(new Certificate(Certificate::TYPE_E, base64_decode('pub_bank_e'), base64_decode('priv_bank_e'), base64_decode('content_bank_e')));
        $keyRing->setBankCertificateX(new Certificate(Certificate::TYPE_X, base64_decode('pub_bank_x'), base64_decode('priv_bank_x'), base64_decode('content_bank_x')));

        self::assertEquals($keyRing, KeyRingFactory::buildKeyRingFromData([
            KeyRingFactory::USER_PREFIX => [
                KeyRingFactory::CERTIFICATE_PREFIX_A => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => 'pub_user_a',
                    KeyRingFactory::CERTIFICATE_PREFIX => 'content_user_a',
                    KeyRingFactory::PRIVATE_KEY_PREFIX => 'priv_user_a',
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_E => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => 'pub_user_e',
                    KeyRingFactory::CERTIFICATE_PREFIX => 'content_user_e',
                    KeyRingFactory::PRIVATE_KEY_PREFIX => 'priv_user_e',
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_X => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => 'pub_user_x',
                    KeyRingFactory::CERTIFICATE_PREFIX => 'content_user_x',
                    KeyRingFactory::PRIVATE_KEY_PREFIX => 'priv_user_x',
                ],
            ],
            KeyRingFactory::BANK_PREFIX => [
                KeyRingFactory::CERTIFICATE_PREFIX_E => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => 'pub_bank_e',
                    KeyRingFactory::CERTIFICATE_PREFIX => 'content_bank_e',
                    KeyRingFactory::PRIVATE_KEY_PREFIX => 'priv_bank_e',
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_X => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => 'pub_bank_x',
                    KeyRingFactory::CERTIFICATE_PREFIX => 'content_bank_x',
                    KeyRingFactory::PRIVATE_KEY_PREFIX => 'priv_bank_x',
                ],
            ],
        ]));
    }
}
