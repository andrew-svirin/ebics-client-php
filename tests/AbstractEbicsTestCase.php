<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\KeyRingManager;
use PHPUnit\Framework\TestCase;

/**
 * Class TestCase extends basic TestCase for add extra setups.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class AbstractEbicsTestCase extends TestCase
{

    protected $data = __DIR__ . '/_data';

    protected $fixtures = __DIR__ . '/_fixtures';

    protected function setupClient(int $credentialsId, X509GeneratorInterface $x509Generator = null): EbicsClient
    {
        $credentials = $this->credentialsDataProvider($credentialsId);

        $bank = new Bank($credentials['hostId'], $credentials['hostURL'], $credentials['hostIsCertified']);
        $user = new User($credentials['partnerId'], $credentials['userId']);
        $keyRingManager = $this->setupKeyKeyRingManager($credentialsId);
        $keyRing = $keyRingManager->loadKeyRing();

        $ebicsClient = new EbicsClient($bank, $user, $keyRing);
        $ebicsClient->setX509Generator($x509Generator);
        return $ebicsClient;
    }

    protected function setupKeyKeyRingManager($credentialsId): KeyRingManager
    {
        $keyRingRealPath = sprintf('%s/workspace/keyring_%d.json', $this->data, $credentialsId);
        return new KeyRingManager($keyRingRealPath, 'test123');
    }

    protected function setupKeys(KeyRing $keyRing)
    {
        $keys = json_decode(file_get_contents($this->fixtures . '/keys.json'));
        $keyRing->setPassword('mysecret');
        $keyRing->setUserCertificateX(new Certificate(
            $keyRing->getUserCertificateX()->getType(),
            $keyRing->getUserCertificateX()->getPublicKey(),
            $keys->X002,
            $keyRing->getUserCertificateX()->getContent()
        ));
        $keyRing->setUserCertificateE(new Certificate(
            $keyRing->getUserCertificateE()->getType(),
            $keyRing->getUserCertificateE()->getPublicKey(),
            $keys->E002,
            $keyRing->getUserCertificateX()->getContent()
        ));
        $keyRing->setUserCertificateA(new Certificate(
            $keyRing->getUserCertificateA()->getType(),
            $keyRing->getUserCertificateA()->getPublicKey(),
            $keys->A006,
            $keyRing->getUserCertificateX()->getContent()
        ));
    }

    /**
     * Validate response data is Ok.
     *
     * @param string $code
     * @param string $reportText
     *
     * @return void
     */
    protected function assertResponseOk(string $code, string $reportText)
    {
        $this->assertEquals('000000', $code, $reportText);
    }

    /**
     * Validate response data is Done.
     *
     * @param string $code
     * @param string $reportText
     *
     * @return void
     */
    protected function assertResponseDone(string $code, string $reportText)
    {
        $this->assertEquals('011000', $code, $reportText);
    }

    protected function assertExceptionCode(string $code = null)
    {
        if (null !== $code) {
            $this->expectExceptionCode((int)$code);
        }
    }

    /**
     * Client credentials data provider.
     *
     * @param int $credentialsId
     *
     * @return array
     */
    public function credentialsDataProvider(int $credentialsId): array
    {
        $path = sprintf('%s/credentials/credentials_%d.json', $this->data, $credentialsId);
        $credentialsEnc = json_decode(file_get_contents($path), true);

        return [
            'hostId' => $credentialsEnc['hostId'],
            'hostURL' => $credentialsEnc['hostURL'],
            'hostIsCertified' => (bool)$credentialsEnc['hostIsCertified'],
            'partnerId' => $credentialsEnc['partnerId'],
            'userId' => $credentialsEnc['userId'],
        ];
    }
}
