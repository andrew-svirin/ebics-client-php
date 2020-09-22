<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Factories\KeyRingFactory;

use AndrewSvirin\Ebics\Factories\KeyRingFactory;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use PHPUnit\Framework\TestCase;

use function base64_encode;

/**
 * @coversDefaultClass KeyRingFactory:
 */
class BuildDataFromKeyRingTest extends TestCase
{
    public function testOkEmpty(): void
    {
        self::assertEquals([
            KeyRingFactory::USER_PREFIX => [
                KeyRingFactory::CERTIFICATE_PREFIX_A => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => null,
                    KeyRingFactory::CERTIFICATE_PREFIX => null,
                    KeyRingFactory::PRIVATE_KEY_PREFIX => null,
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_E => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => null,
                    KeyRingFactory::CERTIFICATE_PREFIX => null,
                    KeyRingFactory::PRIVATE_KEY_PREFIX => null,
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_X => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => null,
                    KeyRingFactory::CERTIFICATE_PREFIX => null,
                    KeyRingFactory::PRIVATE_KEY_PREFIX => null,
                ],
            ],
            KeyRingFactory::BANK_PREFIX => [
                KeyRingFactory::CERTIFICATE_PREFIX_E => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => null,
                    KeyRingFactory::CERTIFICATE_PREFIX => null,
                    KeyRingFactory::PRIVATE_KEY_PREFIX => null,
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_X => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => null,
                    KeyRingFactory::CERTIFICATE_PREFIX => null,
                    KeyRingFactory::PRIVATE_KEY_PREFIX => null,
                ],
            ],
        ], KeyRingFactory::buildDataFromKeyRing(new KeyRing()));
    }

    public function testOkWithData(): void
    {
        $keyRing = new KeyRing();
        $keyRing->setUserCertificateA(new Certificate(Certificate::TYPE_A, 'pub_user_a', 'priv_user_a', 'content_user_a'));
        $keyRing->setUserCertificateE(new Certificate(Certificate::TYPE_E, 'pub_user_e', 'priv_user_e', 'content_user_e'));
        $keyRing->setUserCertificateX(new Certificate(Certificate::TYPE_X, 'pub_user_x', 'priv_user_x', 'content_user_x'));
        $keyRing->setBankCertificateE(new Certificate(Certificate::TYPE_E, 'pub_bank_e', 'priv_bank_e', 'content_bank_e'));
        $keyRing->setBankCertificateX(new Certificate(Certificate::TYPE_X, 'pub_bank_x', 'priv_bank_x', 'content_bank_x'));

        self::assertEquals([
            KeyRingFactory::USER_PREFIX => [
                KeyRingFactory::CERTIFICATE_PREFIX_A => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => base64_encode('pub_user_a'),
                    KeyRingFactory::CERTIFICATE_PREFIX => base64_encode('content_user_a'),
                    KeyRingFactory::PRIVATE_KEY_PREFIX => base64_encode('priv_user_a'),
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_E => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => base64_encode('pub_user_e'),
                    KeyRingFactory::CERTIFICATE_PREFIX => base64_encode('content_user_e'),
                    KeyRingFactory::PRIVATE_KEY_PREFIX => base64_encode('priv_user_e'),
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_X => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => base64_encode('pub_user_x'),
                    KeyRingFactory::CERTIFICATE_PREFIX => base64_encode('content_user_x'),
                    KeyRingFactory::PRIVATE_KEY_PREFIX => base64_encode('priv_user_x'),
                ],
            ],
            KeyRingFactory::BANK_PREFIX => [
                KeyRingFactory::CERTIFICATE_PREFIX_E => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => base64_encode('pub_bank_e'),
                    KeyRingFactory::CERTIFICATE_PREFIX => base64_encode('content_bank_e'),
                    KeyRingFactory::PRIVATE_KEY_PREFIX => base64_encode('priv_bank_e'),
                ],
                KeyRingFactory::CERTIFICATE_PREFIX_X => [
                    KeyRingFactory::PUBLIC_KEY_PREFIX => base64_encode('pub_bank_x'),
                    KeyRingFactory::CERTIFICATE_PREFIX => base64_encode('content_bank_x'),
                    KeyRingFactory::PRIVATE_KEY_PREFIX => base64_encode('priv_bank_x'),
                ],
            ],
        ], KeyRingFactory::buildDataFromKeyRing($keyRing));
    }
}
