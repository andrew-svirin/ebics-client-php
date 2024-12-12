<?php

namespace EbicsApi\Ebics\Tests;

use EbicsApi\Ebics\Contexts\BTDContext;
use EbicsApi\Ebics\Contexts\BTUContext;
use EbicsApi\Ebics\Contexts\HVDContext;
use EbicsApi\Ebics\Contexts\HVEContext;
use EbicsApi\Ebics\Contexts\HVTContext;
use EbicsApi\Ebics\Contexts\RequestContext;
use EbicsApi\Ebics\Exceptions\InvalidUserOrUserStateException;
use EbicsApi\Ebics\Factories\DocumentFactory;
use DateTime;

/**
 * Class EbicsClientTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group ebics-client
 */
class EbicsClientV30Test extends AbstractEbicsTestCase
{
    /**
     * @dataProvider serversDataProvider
     *
     * @group check-keyring
     */
    public function testCheckKeyring(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId);

        $this->assertTrue($client->checkKeyring());

        $keyring = $client->getKeyring();
        $keyring->setPassword('incorrect_password');

        $this->assertFalse($client->checkKeyring());
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group change-keyring-password
     */
    public function testChangeKeyringPassword(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId);

        $client->changeKeyringPassword('some_new_password');

        $hpb = $client->HPB();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group INI-CUSTOM
     * @group INI-V30-CUSTOM
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testINIWithCustomCrt(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['INI']['fake']);

        $client->createUserSignatures('A005', [
            'privatekey' => file_get_contents($this->data.'/electronic_signature/user.key'),
            'certificate' => file_get_contents($this->data.'/electronic_signature/user.crt'),
            'password' => file_get_contents($this->data.'/electronic_signature/passphrase.txt'),
        ]);

        // Check that keyring is empty and or wait on success or wait on exception.
        $userExists = $client->getKeyring()->getUserSignatureA();
        if ($userExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $ini = $client->INI();
        if (!$userExists) {
            $responseHandler = $client->getResponseHandler();
            $this->saveKeyring($credentialsId, $client->getKeyring());
            $code = $responseHandler->retrieveH00XReturnCode($ini);
            $reportText = $responseHandler->retrieveH00XReportText($ini);
            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HEV
     * @group V3
     * @group HEV-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHEV(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HEV']['fake']);
        $hev = $client->HEV();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH000ReturnCode($hev);
        $reportText = $responseHandler->retrieveH000ReportText($hev);
        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group INI
     * @group INI-V30
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testINI(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['INI']['fake']);

        // Check that keyring is empty and or wait on success or wait on exception.
        $userExists = $client->getKeyring()->getUserSignatureA();
        if ($userExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $ini = $client->INI();
        if (!$userExists) {
            $responseHandler = $client->getResponseHandler();
            $this->saveKeyring($credentialsId, $client->getKeyring());
            $code = $responseHandler->retrieveH00XReturnCode($ini);
            $reportText = $responseHandler->retrieveH00XReportText($ini);
            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HIA
     * @group HIA-V30
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHIA(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HIA']['fake']);

        // Check that keyring is empty and or wait on success or wait on exception.
        $bankExists = $client->getKeyring()->getUserSignatureX();
        if ($bankExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $hia = $client->HIA();
        if (!$bankExists) {
            $responseHandler = $client->getResponseHandler();
            $this->saveKeyring($credentialsId, $client->getKeyring());
            $code = $responseHandler->retrieveH00XReturnCode($hia);
            $reportText = $responseHandler->retrieveH00XReportText($hia);
            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group H3K
     * @group V3
     * @group H3K-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testH3K(int $credentialsId, array $codes)
    {
        if (false === isset($codes['H3K'])) {
            $this->markTestSkipped(sprintf('No H3K test for bank credential %d', $credentialsId));
        }

        $client = $this->setupClientV30($credentialsId, $codes['H3K']['fake']);

        // Check that keyring is empty and or wait on success or wait on exception.
        $bankExists = $client->getKeyring()->getUserSignatureX();
        if ($bankExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $hia = $client->H3K();
        if (!$bankExists) {
            $responseHandler = $client->getResponseHandler();
            $this->saveKeyring($credentialsId, $client->getKeyring());
            $code = $responseHandler->retrieveH00XReturnCode($hia);
            $reportText = $responseHandler->retrieveH00XReportText($hia);
            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * Run first HIA and Activate account in bank panel.
     *
     * @dataProvider serversDataProvider
     *
     * @group HPB
     * @group V3
     * @group HPB-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHPB(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HPB']['fake']);

        $this->assertExceptionCode($codes['HPB']['code']);

        $hpb = $client->HPB();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);
        $this->saveKeyring($credentialsId, $client->getKeyring());
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group SPR
     * @group SPR-V30
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testSPR(int $credentialsId, array $codes)
    {
        $this->markTestSkipped('Avoid keyring suspension.');

        $client = $this->setupClientV30($credentialsId, $codes['SPR']['fake']);

        $this->assertExceptionCode($codes['SPR']['code']);
        $spr = $client->SPR();

        $responseHandler = $client->getResponseHandler();

        $code = $responseHandler->retrieveH00XReturnCode($spr->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($spr->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HKD
     * @group HKD-V3
     * @group V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHKD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HKD']['fake']);

        $this->assertExceptionCode($codes['HKD']['code']);
        $hkd = $client->HKD();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hkd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hkd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hkd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hkd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HPD
     * @group V3
     * @group HPD-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHPD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HPD']['fake']);

        $this->assertExceptionCode($codes['HPD']['code']);
        $hpd = $client->HPD();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hpd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hpd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hpd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hpd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HAA
     * @group V3
     * @group HAA-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHAA(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HAA']['fake']);

        $this->assertExceptionCode($codes['HAA']['code']);
        $haa = $client->HAA();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($haa->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($haa->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($haa->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($haa->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group BTD
     * @group V3
     * @group BTD-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testBTD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['BTD']['fake']);

        $context = new BTDContext();
        $context->setServiceName('PSR');
        $context->setMsgName('pain.002');
        $context->setMsgNameVersion('03');
        $context->setScope('CH');
        $context->setContainerType('ZIP');

        $this->assertExceptionCode($codes['BTD']['code']);
        $btd = $client->BTD($context, new DateTime('2020-03-21'), new DateTime('2020-04-21'));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($btd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($btd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($btd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($btd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group PTK
     * @group V3
     * @group PTK-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testPTK(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['PTK']['fake']);

        $this->assertExceptionCode($codes['PTK']['code']);
        $ptk = $client->PTK();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($ptk->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($ptk->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($ptk->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($ptk->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group C52
     * @group V3
     * @group C52-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testC52(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['C52']['fake']);

        $this->assertExceptionCode($codes['C52']['code']);
        $c52 = $client->C52(
            new DateTime('2020-03-21'),
            new DateTime('2020-04-21')
        );

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($c52->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($c52->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($c52->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($c52->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group C53
     * @group V3
     * @group C53-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testC53(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['C53']['fake']);

        $this->assertExceptionCode($codes['C53']['code']);
        $c53 = $client->C53(
            new DateTime('2020-03-21'),
            new DateTime('2020-04-21')
        );

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($c53->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($c53->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($c53->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($c53->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group C54
     * @group V3
     * @group C54-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testC54(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['C54']['fake']);

        $this->assertExceptionCode($codes['C54']['code']);
        $c54 = $client->C54(
            new DateTime('2020-03-21'),
            new DateTime('2020-04-21')
        );

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($c54->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($c54->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($c54->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($c54->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group Z54
     * @group V3
     * @group Z54-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testZ54(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['Z54']['fake']);

        $this->assertExceptionCode($codes['Z54']['code']);
        $z54 = $client->Z54(
            new DateTime('2020-03-21'),
            new DateTime('2020-04-21'),
            (new RequestContext())
                ->setBTDContext((new BTDContext())
                    ->setScope($codes['Z54']['params']['s'])
                    ->setMsgNameVersion($codes['Z54']['params']['mnv']))
        );

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($z54->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($z54->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($z54->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($z54->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group ZSR
     * @group V3
     * @group ZSR-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testZSR(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['ZSR']['fake']);

        $this->assertExceptionCode($codes['ZSR']['code']);
        $zsr = $client->ZSR(
            new DateTime('2020-03-21'),
            new DateTime('2020-04-21')
        );

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($zsr->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($zsr->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($zsr->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($zsr->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group XEK
     * @group V3
     * @group XEK-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testXEK(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['XEK']['fake']);

        $this->assertExceptionCode($codes['XEK']['code']);
        $xek = $client->XEK(
            new DateTime('2020-03-21'),
            new DateTime('2020-04-21')
        );

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($xek->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xek->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($xek->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($xek->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group BTU
     * @group V3
     * @group BTU-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testBTU(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['BTU']['fake']);

        $this->assertExceptionCode($codes['BTU']['code']);

        $orderData = $this->buildCustomerCreditTransfer('urn:iso:std:iso:20022:tech:xsd:pain.001.001.09');

        // XE2
        $context = new BTUContext();
        $context->setServiceName('MCT');
        $context->setScope('CH');
        $context->setMsgName('pain.001');
        $context->setMsgNameVersion('09');
        $context->setFileName('xe2.pain001.xml');

        $btu = $client->BTU($context, $orderData);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($btu->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($btu->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($btu->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($btu->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group XE3
     * @group V3
     * @group XE3-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testXE3(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['XE3']['fake']);

        $this->assertExceptionCode($codes['XE3']['code']);

        $documentFactory = new DocumentFactory();
        $orderData = $documentFactory->create(file_get_contents($this->fixtures.'/yct.pain001.xml'));

        $xe3 = $client->XE3($orderData, (new RequestContext())
            ->setBTUContext((new BTUContext())
                ->setScope($codes['XE3']['params']['s'])
                ->setMsgNameVersion($codes['XE3']['params']['mnv'])
            ));

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($xe3->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xe3->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($xe3->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xe3->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group YCT
     * @group V3
     * @group YCT-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testYCT(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['YCT']['fake']);

        $this->assertExceptionCode($codes['YCT']['code']);

        $documentFactory = new DocumentFactory();
        $orderData = $documentFactory->create(file_get_contents($this->fixtures.'/yct.pain001.xml'));

        $yct = $client->YCT($orderData);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($yct->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($yct->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($yct->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($yct->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVU
     * @group V3
     * @group HVU-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVU(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HVU']['fake']);

        $this->assertExceptionCode($codes['HVU']['code']);
        $hvu = $client->HVU();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hvu->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hvu->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hvu->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hvu->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVZ
     * @group V3
     * @group HVZ-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVZ(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HVZ']['fake']);

        $this->assertExceptionCode($codes['HVZ']['code']);
        $hvz = $client->HVZ();

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hvz->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hvz->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hvz->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hvz->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVE
     * @group V3
     * @group HVE-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVE(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HVE']['fake']);

        $this->assertExceptionCode($codes['HVE']['code']);

        $context = new HVEContext();
        $context->setOrderId('V234');
        $context->setServiceName('SDD');
        $context->setOrderType('CDX');
        $context->setScope('DE');
        $context->setServiceOption('0CDX');
        $context->setMsgName('pain.008');
        $context->setPartnerId('PARTNERPK56');
        $context->setDigest('--digset--');

        $hve = $client->HVE($context);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hve->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hve->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hve->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hve->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVD
     * @group V3
     * @group HVD-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVD(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HVD']['fake']);

        $this->assertExceptionCode($codes['HVD']['code']);

        $context = new HVDContext();
        $context->setOrderId('V234');
        $context->setServiceName('SDD');
        $context->setOrderType('CDX');
        $context->setScope('DE');
        $context->setServiceOption('0CDX');
        $context->setOrderType('HVD');
        $context->setMsgName('pain.008');
        $context->setPartnerId('PARTNERPK56');

        $hvd = $client->HVD($context);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hvd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hvd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hvd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hvd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HVT
     * @group V3
     * @group HVT-V3
     *
     * @param int $credentialsId
     * @param array $codes
     *
     * @covers
     */
    public function testHVT(int $credentialsId, array $codes)
    {
        $client = $this->setupClientV30($credentialsId, $codes['HVT']['fake']);

        $this->assertExceptionCode($codes['HVT']['code']);

        $context = new HVTContext();
        $context->setOrderId('V234');
        $context->setServiceName('SDD');
        $context->setOrderType('HVT');
        $context->setScope('DE');
        $context->setServiceOption('0CDX');
        $context->setMsgName('pain.008');
        $context->setPartnerId('PARTNERPK56');
        $context->setCompleteOrderData(false);
        $context->setFetchLimit(1);
        $context->setFetchOffset(0);

        $hvt = $client->HVT($context);

        $responseHandler = $client->getResponseHandler();
        $code = $responseHandler->retrieveH00XReturnCode($hvt->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hvt->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($hvt->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($hvt->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * Provider for servers.
     */
    public function serversDataProvider()
    {
        return [
            [
                6, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'SPR' => ['code' => null, 'fake' => false],
                    'H3K' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HVU' => ['code' => '090003', 'fake' => false],
                    'HVZ' => ['code' => '090003', 'fake' => false],
                    'HVE' => ['code' => '090003', 'fake' => false],
                    'HVD' => ['code' => '090003', 'fake' => false],
                    'HVT' => ['code' => '090003', 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'C52' => ['code' => '091005', 'fake' => false],
                    'C53' => ['code' => '090005', 'fake' => false],
                    'C54' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '091005', 'fake' => false, 'params' => ['mnv' => '04', 's' => 'CH']],
                    'ZSR' => ['code' => '091005', 'fake' => false],
                    'XEK' => ['code' => '091005', 'fake' => false],
                    'BTD' => ['code' => '090005', 'fake' => false],
                    'BTU' => ['code' => null, 'fake' => false],
                    'XE3' => ['code' => null, 'fake' => false, 'params' => ['mnv' => '02', 's' => 'CH']],
                    'YCT' => ['code' => '091005', 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                ],
            ],
            [
                7, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'SPR' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HVU' => ['code' => '090003', 'fake' => false],
                    'HVZ' => ['code' => '090003', 'fake' => false],
                    'HVE' => ['code' => '090003', 'fake' => false],
                    'HVD' => ['code' => '090003', 'fake' => false],
                    'HVT' => ['code' => '090003', 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'C52' => ['code' => '091005', 'fake' => false],
                    'C53' => ['code' => '091005', 'fake' => false],
                    'C54' => ['code' => '091005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false, 'params' => ['mnv' => '04', 's' => 'CH']],
                    'ZSR' => ['code' => '091005', 'fake' => false],
                    'XEK' => ['code' => '091005', 'fake' => false],
                    'BTD' => ['code' => '090005', 'fake' => false],
                    'BTU' => ['code' => null, 'fake' => false],
                    'XE3' => ['code' => null, 'fake' => false, 'params' => ['mnv' => '02', 's' => 'CH']],
                    'YCT' => ['code' => '091005', 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                ],
            ],
        ];
    }
}
