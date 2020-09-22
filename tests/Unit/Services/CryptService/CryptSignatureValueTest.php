<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Unit\Services\CryptService;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

use function bin2hex;
use function ctype_print;

/**
 * @coversDefaultClass CryptService
 */
class CryptSignatureValueTest extends TestCase
{
    public function testUserCertificateXEmpty(): void
    {
        $sUT     = new CryptService();
        $keyring = new KeyRing();

        self::expectException(EbicsException::class);
        self::expectExceptionMessage('On this stage must persist certificate for authorization. Run INI and HIA requests for retrieve them. (missing user certificat X)');

        $sUT->cryptSignatureValue($keyring, 'test');
    }

    public function testUserCertificateXPrivateKeyEmpty(): void
    {
        $sUT     = new CryptService();
        $keyring = new KeyRing();
        $keyring->setUserCertificateX(new Certificate('test', 'test'));

        self::expectException(EbicsException::class);
        self::expectExceptionMessage('On this stage must persist certificate for authorization. Run INI and HIA requests for retrieve them. (missing user certificat X)');

        $sUT->cryptSignatureValue($keyring, 'test');
    }

    public function testNotEncryped(): void
    {
        $sUT     = new CryptService();
        $keyring = new KeyRing();
        $keyring->setPassword('test');
        $keyring->setUserCertificateX(new Certificate('test', 'test', 'test'));

        self::expectException(EbicsException::class);
        self::expectExceptionMessage('Incorrect authorization.');

        $sUT->cryptSignatureValue($keyring, 'test');
    }

    public function testOk(): void
    {
        $sUT     = new CryptService();
        $keyring = new KeyRing();
        $keyring->setPassword('');
        $keyring->setUserCertificateX(new Certificate('test', '-----BEGIN PUBLIC KEY-----
MIGeMA0GCSqGSIb3DQEBAQUAA4GMADCBiAKBgF9279lHu08UA010rC75LDAe0gDL
9xYv7C1pxSYc17nFI9tAME9nSunFc8Hgy2sLbkNeeUI6eeASlFLjQd83q7kukLya
dx6hHa27rFqPiBLh3tjlAXwQfAkmx27Jzds6636KCShwo/TYEahOFCKgP+P4sK+p
OgZ/eIEQC/b5e2LRAgMBAAE=
-----END PUBLIC KEY-----', '-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgF9279lHu08UA010rC75LDAe0gDL9xYv7C1pxSYc17nFI9tAME9n
SunFc8Hgy2sLbkNeeUI6eeASlFLjQd83q7kukLyadx6hHa27rFqPiBLh3tjlAXwQ
fAkmx27Jzds6636KCShwo/TYEahOFCKgP+P4sK+pOgZ/eIEQC/b5e2LRAgMBAAEC
gYAsdh1xKfpv8xcyrONAoWZWJxSRsG0s1Tb/U6KxhH9okwuHItcdNDNsuzyVkJfN
DC7Xi5mYIdn/ZUfVbuiQCMKeU+3YDrcM/nlW18U6BRLdR2zFzx36wEaBkVRLGt3/
T1S/GgRh5GrKatkLoWWjVwDATHPDPlXH5uVI+y0xVfhP/QJBAKPGC+KKcNAt7I7q
VV1gtUHwUtPG1F2ugJWUXcx9rCZsf/lrQUvE+3jYj9u3Pxg8FZL+05rB763Ki+Ll
b2IO2PsCQQCVOVEGb3haLjgclBxosBKiEex7uwM7upJOVqqBhuIZr+36J/9gR5Ri
veJ9GsAMZXjBMpzgI3HBQYe0LoBitsGjAkEAjnuJ8YDSXzvlF/1lYUT2zTRBS0Ar
mIluEzSuWm9nq1IwEJYwi7QHuH5owhXuDa6Qcn/DJ1vcow2ZoEBOJDiYqwJAUwtq
xCIU4FsIbx8eEESsmfVPniwdSIg0E9S3Xw4plhIKZkUMIhCzy5/RA753Um7GHP6F
v1b6X4qQcv3OBSGf8QJAZC1CjeJ0/cgJGnnC9rHNZLgIv9Ei/SuQK7K1/J9Pz46E
8hApJw192+XQJHGgDDPeSWWllDDIDe4/UKKSrHCclg==
-----END RSA PRIVATE KEY-----'));

        $result = $sUT->cryptSignatureValue($keyring, 'test');

        self::assertIsString($result);
        self::assertFalse(ctype_print($result)); // binary
        self::assertSame(
            '3232c62266b55a8c06a95534de5350c56c53311f2b26769e7f5ca362cffac84f8c9cc82bd4765dd86e9eb7c685346a458a544911bbdccf0ecbcd53b8e5e8612e3610ff36d43675ebbba85b64f2798f81c068e719986c3ad5fd6526b558c83b0b6565733ffa875bed6b3f593e51bbb0dcc1d78bc755c1bc9a2037d5fc0edd0698',
            bin2hex($result)
        );
    }
}
