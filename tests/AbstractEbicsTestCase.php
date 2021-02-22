<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Contracts\EbicsClientInterface;
use AndrewSvirin\Ebics\Contracts\KeyRingManagerInterface;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Factories\SignatureFactory;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\KeyRingManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

    protected function setupClient(
        int $credentialsId,
        X509GeneratorInterface $x509Generator = null,
        $fake = false
    ): EbicsClientInterface {
        $credentials = $this->credentialsDataProvider($credentialsId);

        $bank = new Bank($credentials['hostId'], $credentials['hostURL']);
        $bank->setIsCertified($credentials['hostIsCertified']);
        $bank->setServerName(sprintf('Server %d', $credentialsId));
        $user = new User($credentials['partnerId'], $credentials['userId']);
        $keyRingManager = $this->setupKeyKeyRingManager($credentialsId);
        $keyRing = $keyRingManager->loadKeyRing();

        $ebicsClient = new EbicsClient($bank, $user, $keyRing);
        $ebicsClient->setX509Generator($x509Generator);

        if (true === $fake) {
            $ebicsClient->setHttpClient(new FakerHttpClient($this->fixtures));
        }

        return $ebicsClient;
    }

    protected function setupKeyKeyRingManager($credentialsId): KeyRingManagerInterface
    {
        $keyRingRealPath = sprintf('%s/workspace/keyring_%d.json', $this->data, $credentialsId);
        return new KeyRingManager($keyRingRealPath, 'test123');
    }

    protected function setupKeys(KeyRing $keyRing)
    {
        $keys = json_decode(file_get_contents($this->fixtures . '/keys.json'));
        $keyRing->setPassword('mysecret');
        $signatureFactory = new SignatureFactory();

        $userSignatureA = $signatureFactory->createSignatureA($keyRing->getUserSignatureA()->getPublicKey(),
            $keys->A006);
        $userSignatureA->setCertificateContent($keyRing->getUserSignatureA()->getCertificateContent());
        $keyRing->setUserSignatureA($userSignatureA);

        $userSignatureE = $signatureFactory->createSignatureE($keyRing->getUserSignatureE()->getPublicKey(),
            $keys->E002);
        $userSignatureE->setCertificateContent($keyRing->getUserSignatureE()->getCertificateContent());
        $keyRing->setUserSignatureE($userSignatureE);

        $userSignatureX = $signatureFactory->createSignatureX($keyRing->getUserSignatureX()->getPublicKey(),
            $keys->X002);
        $userSignatureX->setCertificateContent($keyRing->getUserSignatureX()->getCertificateContent());
        $keyRing->setUserSignatureX($userSignatureX);
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

        if (!file_exists($path)) {
            throw new RuntimeException('Credentials missing');
        }

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
