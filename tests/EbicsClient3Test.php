<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Exceptions\InvalidUserOrUserStateException;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class EbicsTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group SERVER-3
 */
class EbicsClient3Test extends AbstractEbicsTestCase
{

    /**
     * @var int
     */
    protected $credentialsId = 3;

    /**
     * @throws EbicsException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setupClient();
    }

    /**
     * @group HEV
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHEV()
    {
        $hev = $this->client->HEV();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH000ReturnCode($hev);
        $reportText = $responseHandler->retrieveH000ReportText($hev);
        $this->assertResponseCorrect($code, $reportText);
    }

    /**
     * @group INI
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testINI()
    {
        // Check that keyring is empty and or wait on success or wait on exception.
        $userExists = $this->keyRing->getUserCertificateA();
        if ($userExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
            $this->expectExceptionMessage('[EBICS_INVALID_USER_OR_USER_STATE] Teilnehmer unbekannt oder Teilnehmerzustand unzulässig');
        }
        $ini = $this->client->INI();
        if (!$userExists) {
            $responseHandler = new ResponseHandler();
            $this->keyRingManager->saveKeyRing($this->keyRing);
            $code = $responseHandler->retrieveH004ReturnCode($ini);
            $reportText = $responseHandler->retrieveH004ReportText($ini);
            $this->assertResponseCorrect($code, $reportText);
        }
    }

    /**
     * @group HIA
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHIA()
    {
        // Check that keyring is empty and or wait on success or wait on exception.
        $bankExists = $this->keyRing->getUserCertificateX();
        if ($bankExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
            $this->expectExceptionMessage('[EBICS_INVALID_USER_OR_USER_STATE] Teilnehmer unbekannt oder Teilnehmerzustand unzulässig');
        }
        $hia = $this->client->HIA();
        if (!$bankExists) {
            $responseHandler = new ResponseHandler();
            $this->keyRingManager->saveKeyRing($this->keyRing);
            $code = $responseHandler->retrieveH004ReturnCode($hia);
            $reportText = $responseHandler->retrieveH004ReportText($hia);
            $this->assertResponseCorrect($code, $reportText);
        }
    }

    /**
     * Run first HIA and Activate account in bank panel.
     *
     * @group HPB
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHPB()
    {
        $hpb = $this->client->HPB();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($hpb);
        $reportText = $responseHandler->retrieveH004ReportText($hpb);
        $this->assertResponseCorrect($code, $reportText);
        $this->keyRingManager->saveKeyRing($this->keyRing);
    }

    /**
     * @depends testHPB
     * @group HPD
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHPD()
    {
        $hpd = $this->client->HPD();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($hpd);
        $reportText = $responseHandler->retrieveH004ReportText($hpd);
        $this->assertResponseCorrect($code, $reportText);
    }

    /**
     * @group HKD
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHKD()
    {
        $responseHandler = new ResponseHandler();
        $hkd = $this->client->HKD();
        $code = $responseHandler->retrieveH004ReturnCode($hkd);
        $reportText = $responseHandler->retrieveH004ReportText($hkd);
        $this->assertResponseCorrect($code, $reportText);
    }

    /**
     * @group HAA
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHAA()
    {
        $haa = $this->client->HAA();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($haa);
        $reportText = $responseHandler->retrieveH004ReportText($haa);
        $this->assertResponseCorrect($code, $reportText);
    }

    /**
     * @group HTD
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHTD()
    {
        $htd = $this->client->HTD();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($htd);
        $reportText = $responseHandler->retrieveH004ReportText($htd);
        $this->assertResponseCorrect($code, $reportText);
    }

    /**
     * @group VMK
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testVMK()
    {
        $this->expectExceptionCode(91005);
        $this->expectExceptionMessage('[EBICS_INVALID_ORDER_TYPE] Auftragsart unzulässig');
        $this->client->VMK();
    }

    /**
     * @group Z53
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testZ53()
    {
        $this->expectExceptionCode(90005);
        $this->expectExceptionMessage('[EBICS_OK] OK');
        $this->client->Z53();
    }
}
