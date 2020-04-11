<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Exceptions\AuthorisationOrderTypeFailedException;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Exceptions\InvalidUserOrUserStateException;
use AndrewSvirin\Ebics\Exceptions\NoDownloadDataAvailableException;
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
 */
class EbicsClientTest extends AbstractEbicsTestCase
{
    /**
     * @dataProvider credentialsDataProvider
     *
     * @throws EbicsException
     */
    public function setUp()
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
     * @throws EbicsException
     */
    public function testINI()
    {
        $this->expectException(InvalidUserOrUserStateException::class);
        $this->expectExceptionCode('091002');
        $this->expectExceptionMessage('[EBICS_INVALID_USER_OR_USER_STATE] Teilnehmer unbekannt oder Teilnehmerzustand unzulässig');
        $ini = $this->client->INI();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($ini);
        $reportText = $responseHandler->retrieveH004ReportText($ini);
        $this->assertResponseCorrect($code, $reportText);
        $this->keyRingManager->saveKeyRing($this->keyRing);
    }

    /**
     * @depends testINI
     * @group HIA
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws EbicsException
     */
    public function testHIA()
    {
        $this->expectException(InvalidUserOrUserStateException::class);
        $this->expectExceptionCode('091002');
        $this->expectExceptionMessage('[EBICS_INVALID_USER_OR_USER_STATE] Teilnehmer unbekannt oder Teilnehmerzustand unzulässig');
        $hia = $this->client->HIA();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($hia);
        $reportText = $responseHandler->retrieveH004ReportText($hia);
        $this->assertResponseCorrect($code, $reportText);
        $this->keyRingManager->saveKeyRing($this->keyRing);
    }

    /**
     * Run first HIA and Activate account in bank panel.
     *
     * @depends testHIA
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
     * @depends testHPB
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
        $this->expectException(AuthorisationOrderTypeFailedException::class);
        $this->expectExceptionCode('090003');
        $this->expectExceptionMessage('[EBICS_OK] OK');
        $hpd = $this->client->HKD();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($hpd);
        $reportText = $responseHandler->retrieveH004ReportText($hpd);
        $this->assertResponseCorrect($code, $reportText);
    }

    /**
     * @depends testHPB
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
     * @depends testHPB
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
        $this->expectException(NoDownloadDataAvailableException::class);
        $this->expectExceptionCode('090005');
        $this->expectExceptionMessage('[EBICS_OK] OK');
        $vmk = $this->client->VMK();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($vmk);
        $reportText = $responseHandler->retrieveH004ReportText($vmk);
        $this->assertResponseCorrect($code, $reportText);
    }

    /**
     * @depends testHPB
     * @group STA
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSTA()
    {
        $this->expectException(NoDownloadDataAvailableException::class);
        $this->expectExceptionCode('090005');
        $this->expectExceptionMessage('[EBICS_OK] OK');
        $sta = $this->client->STA();
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($sta);
        $reportText = $responseHandler->retrieveH004ReportText($sta);
        $this->assertResponseCorrect($code, $reportText);
    }

    /**
     * @depends testHPB
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
}
