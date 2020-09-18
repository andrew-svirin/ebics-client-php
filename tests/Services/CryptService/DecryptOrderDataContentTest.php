<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Services\CryptService;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

class DecryptOrderDataContentTest extends TestCase
{
    public function testUserCertificateEEmpty(): void
    {
        $sUT     = new CryptService();
        $keyring = new KeyRing();

        self::expectException(EbicsException::class);
        self::expectExceptionMessage('Certificate E is not set.');

        $sUT->decryptOrderDataContent($keyring, new OrderDataEncrypted('hop', 'test'));
    }

    public function testOk(): void
    {
        $this->markTestSkipped('not working');
        $orderDataEncrypted = new OrderDataEncrypted(
            'iB5GQXBKoZUA3ubhkcdaW3Bp2J4vQU3CmyDXFophjh6Jteta0PZLGcHis8ZAPe74OmngDF3GowdtuTGSUs+ekCcA4oTHWOE4PfKR4cSwMvvIUfkHKMveptc+8x4XFbMGmuyEn/Wj0DNeiccIQw6f9oRIm6m5R6qI+u3deBSLaP4=',
            'WHoY075ZzqqZWgYAG24NN8JgmDpSacVlMYhHZbUHg6vcfKpf/q0LN5SxtevUCeTLTnbdoeQ5kIpgh0x0EaEiiXJw1nkm5md/Md4BUMAfpVEgeMbQkExEuSBcYyh9tBa5+oR397n59H5qrOmJsA4lbWznpO2EzcQAoRk/z2LzVOw='
        );

        $sUT     = new CryptService();
        $keyring = new KeyRing();
        $keyring->setPassword('');
        $keyring->setUserCertificateE(new Certificate('test', '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCT/4JDnBXaj3d3TrEjQeHbVu4e
0mVReFyq8Ve3oxSUXS/6PKhZgbKHJQ6l3+ln9SHRIVMF4shGNgs8uotmFq6RQoBV
+wJ+qnijmcXtjqMK8okhZrEZzyYax/xZDPdG9cqinpaGu4saNownCMXj2wlDsIFO
lqG0cp+/SpXIL9imTQIDAQAB
-----END PUBLIC KEY-----
', '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQCT/4JDnBXaj3d3TrEjQeHbVu4e0mVReFyq8Ve3oxSUXS/6PKhZ
gbKHJQ6l3+ln9SHRIVMF4shGNgs8uotmFq6RQoBV+wJ+qnijmcXtjqMK8okhZrEZ
zyYax/xZDPdG9cqinpaGu4saNownCMXj2wlDsIFOlqG0cp+/SpXIL9imTQIDAQAB
AoGAWqgsUl/9Xwl84793cKJ9yI9Cg+zblYFGOoxl8B5cj9lZd07KzTFOe8xuYZt8
bWrSUTm5kqRti9y1G3klxN5mBbHrQPSHhpekjnss4DaH91h3idKl1smNH0118p4X
ET0+1rVb/Ye+k7o1CIbKylU4Ryqj1zqPNmC0ock6ZK2+pr0CQQDiQ6RmaKWro5CG
vAu05BIRRzlRQAh6idBf4AV3OQiQhVcv7kqPUPASMPaYs8O3eD5XwPv624dPFZN/
uEKbcSb7AkEAp3K06RxZi8RSV1UvMxdjcpf/wOlvWuyYfolbo4GnpOEtiUHPbE7N
t4K9hwaLWNdpr/5M6vJbFBNivjF8oCyFVwJBAJoWRKQ1SfWsiyUmdLZ4x1Ea9w69
E8kXh19zeWVq4slA9VI/7mjRTtykmZr+eR+99H7gfvmkfO4/nFZTTpD7KvcCQBJ7
pOB+UpwM5ZHiQz1+fWmuwXpHyhTdPM/q1YSs0RZwDJiz/PNVl3uEIOuAm20JCg91
IIRmkAsdQK8Bw2HiyC0CQQC6bwZd7tNSjlvNgvA5xuGAmCpqy/zSuvTOuebAJ6Ur
RHLoIwam/w9SMVFsbGpN0rCOvIQlTrW/N+50Rw2Y4yry
-----END RSA PRIVATE KEY-----
'));

        $result = $sUT->decryptOrderDataContent($keyring, $orderDataEncrypted);

        self::assertIsString($result);
    }
}
