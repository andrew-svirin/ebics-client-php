<?php

namespace AndrewSvirin\Ebics\Tests;

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
    /**
     * Algo to encode/decode session.
     */
    const ENCRYPT_ALGO = 'AES-128-ECB';

    public $data = __DIR__ . '/_data';
    public $fixtures = __DIR__ . '/_fixtures';

    /**
     * @var int
     */
    protected $credentialsId = 1;

    /**
     * @var EbicsClient
     */
    protected $client;

    /**
     * @var KeyRingManager
     */
    protected $keyRingManager;

    /**
     * @var KeyRing
     */
    protected $keyRing;

    /**
     * @var Bank
     */
    protected $bank;

    /**
     * @var User
     */
    protected $user;

    protected function setupClient()
    {
        $credentials = $this->credentialsDataProvider($this->credentialsId);
        $keyRingRealPath = sprintf('%s/workspace/keyring_%d.json', $this->data, $this->credentialsId);
        $this->bank = new Bank($credentials['hostId'], $credentials['hostURL'], $credentials['hostIsCertified']);
        $this->user = new User($credentials['partnerId'], $credentials['userId']);
        $this->keyRingManager = new KeyRingManager($keyRingRealPath, 'test123');
        $this->keyRing = $this->keyRingManager->loadKeyRing();
        $this->client = new EbicsClient($this->bank, $this->user, $this->keyRing);
    }

    protected function setupKeys()
    {
        $keys = json_decode(file_get_contents($this->fixtures . '/keys.json'));
        $this->keyRing->setPassword('mysecret');
        $this->keyRing->setUserCertificateX(new Certificate(
            $this->keyRing->getUserCertificateX()->getType(),
            $this->keyRing->getUserCertificateX()->getPublicKey(),
            $keys->X002,
            $this->keyRing->getUserCertificateX()->getContent()
        ));
        $this->keyRing->setUserCertificateE(new Certificate(
            $this->keyRing->getUserCertificateE()->getType(),
            $this->keyRing->getUserCertificateE()->getPublicKey(),
            $keys->E002,
            $this->keyRing->getUserCertificateX()->getContent()
        ));
        $this->keyRing->setUserCertificateA(new Certificate(
            $this->keyRing->getUserCertificateA()->getType(),
            $this->keyRing->getUserCertificateA()->getPublicKey(),
            $keys->A006,
            $this->keyRing->getUserCertificateX()->getContent()
        ));
    }

    /**
     * Validate response data.
     *
     * @param $code
     * @param $reportText
     *
     * @return void
     */
    protected function assertResponseCorrect($code, $reportText)
    {
        $this->assertEquals($code, '000000', $reportText);
        $this->assertEquals($reportText, '[EBICS_OK] OK');
    }

    /**
     * Client credentials data provider.
     *
     * @param int $credentialsId
     *
     * @return array
     */
    public function credentialsDataProvider($credentialsId): array
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
