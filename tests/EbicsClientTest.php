<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Builders\CustomerCreditTransfer\CustomerCreditTransferBuilder;
use AndrewSvirin\Ebics\Builders\CustomerCreditTransfer\CustomerSwissCreditTransferBuilder;
use AndrewSvirin\Ebics\Builders\CustomerDirectDebit\CustomerDirectDebitBuilder;
use AndrewSvirin\Ebics\Contexts\BTFContext;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Exceptions\InvalidUserOrUserStateException;
use AndrewSvirin\Ebics\Handlers\ResponseHandlerV2;
use AndrewSvirin\Ebics\Handlers\ResponseHandlerV3;
use AndrewSvirin\Ebics\Models\StructuredPostalAddress;
use AndrewSvirin\Ebics\Models\UnstructuredPostalAddress;
use AndrewSvirin\Ebics\Tests\Factories\X509\WeBankX509Generator;
use DateTime;
use Silarhi\Cfonb\CfonbParser;

/**
 * Class EbicsClientTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group ebics-client
 */
class EbicsClientTest extends AbstractEbicsTestCase
{
    /**
     * @dataProvider serversDataProvider
     *
     * @group HEV
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHEV(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['HEV']['fake']);
        $hev = $client->HEV();

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH000ReturnCode($hev);
        $reportText = $responseHandler->retrieveH000ReportText($hev);
        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group INI
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testINI(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['INI']['fake']);

        // Check that keyring is empty and or wait on success or wait on exception.
        $userExists = $client->getKeyRing()->getUserSignatureA();
        if ($userExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $ini = $client->INI();
        if (!$userExists) {
            $responseHandler = new ResponseHandlerV2();
            $keyRingManager = $this->setupKeyKeyRingManager($credentialsId);
            $keyRingManager->saveKeyRing($client->getKeyRing());
            $code = $responseHandler->retrieveH00XReturnCode($ini);
            $reportText = $responseHandler->retrieveH00XReportText($ini);
            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HIA
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHIA(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['HIA']['fake']);

        // Check that keyring is empty and or wait on success or wait on exception.
        $bankExists = $client->getKeyRing()->getUserSignatureX();
        if ($bankExists) {
            $this->expectException(InvalidUserOrUserStateException::class);
            $this->expectExceptionCode(91002);
        }
        $hia = $client->HIA();
        if (!$bankExists) {
            $responseHandler = new ResponseHandlerV2();
            $keyRingManager = $this->setupKeyKeyRingManager($credentialsId);
            $keyRingManager->saveKeyRing($client->getKeyRing());
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
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHPB(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['HPB']['fake']);

        $this->assertExceptionCode($codes['HPB']['code']);

        $hpb = $client->HPB();
        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($hpb->getTransaction()->getInitializationSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);
        $keyRingManager = $this->setupKeyKeyRingManager($credentialsId);
        $keyRingManager->saveKeyRing($client->getKeyRing());
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group HKD
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHKD(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['HKD']['fake']);

        $this->assertExceptionCode($codes['HKD']['code']);
        $hkd = $client->HKD();

        $responseHandler = new ResponseHandlerV2();
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
     * @group HTD
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHTD(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['HTD']['fake']);

        $this->assertExceptionCode($codes['HTD']['code']);
        $htd = $client->HTD();

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($htd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($htd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($htd->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($htd->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group PTK
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testPTK(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['PTK']['fake']);

        $this->assertExceptionCode($codes['PTK']['code']);
        $ptk = $client->PTK();

        $responseHandler = new ResponseHandlerV2();
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
     * @group BTD
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testBTD(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['BTD']['fake']);

        $context = new BTFContext();
        $context->setServiceName('service-name');
        $context->setMsgName('msg-name');

        $this->assertExceptionCode($codes['BTD']['code']);
        $btd = $client->BTD($context);

        $responseHandler = new ResponseHandlerV3();
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
     * @group HPD
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHPD(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['HPD']['fake']);

        $this->assertExceptionCode($codes['HPD']['code']);
        $hpd = $client->HPD();

        $responseHandler = new ResponseHandlerV2();
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
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHAA(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['HAA']['fake']);

        $this->assertExceptionCode($codes['HAA']['code']);
        $haa = $client->HAA();

        $responseHandler = new ResponseHandlerV2();
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
     * @group VMK
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testVMK(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['VMK']['fake']);

        $this->assertExceptionCode($codes['VMK']['code']);
        $vmk = $client->VMK();

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($vmk->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($vmk->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($vmk->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($vmk->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group STA
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testSTA(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['STA']['fake']);

        $this->assertExceptionCode($codes['STA']['code']);
        $sta = $client->STA();

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($sta->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($sta->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($sta->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($sta->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group C52
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testC52(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['C52']['fake']);

        $this->assertExceptionCode($codes['C52']['code']);
        $c52 = $client->C52();

        $responseHandler = new ResponseHandlerV2();
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
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testC53(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['C53']['fake']);

        $this->assertExceptionCode($codes['C53']['code']);
        $c53 = $client->C53(
            new DateTime(),
            (new DateTime())->modify('-30 day'),
            (new DateTime())->modify('-1 day')
        );

        $responseHandler = new ResponseHandlerV2();
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
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testC54(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['C54']['fake']);

        $this->assertExceptionCode($codes['C54']['code']);
        $c54 = $client->C53(
            new DateTime(),
            (new DateTime())->modify('-30 day'),
            (new DateTime())->modify('-1 day')
        );

        $responseHandler = new ResponseHandlerV2();
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
     * @group Z53
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testZ53(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['Z53']['fake']);

        $this->assertExceptionCode($codes['Z53']['code']);
        $z53 = $client->Z53(
            new DateTime(),
            (new DateTime())->modify('-30 day'),
            (new DateTime())->modify('-1 day')
        );

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($z53->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($z53->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($z53->getTransaction()->getReceipt());
        $reportText = $responseHandler->retrieveH00XReportText($z53->getTransaction()->getReceipt());

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group Z54
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testZ54(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['Z54']['fake']);

        $this->assertExceptionCode($codes['Z54']['code']);
        $z54 = $client->Z54(
            new DateTime(),
            (new DateTime())->modify('-30 day'),
            (new DateTime())->modify('-1 day')
        );

        $responseHandler = new ResponseHandlerV2();
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
     * @group FDL
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testFDL(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        foreach ($codes['FDL'] as $format => $code) {
            $client = $this->setupClient($credentialsId, $x509Generator, $code['fake']);

            $this->assertExceptionCode($code['code']);

            $fdl = $client->FDL(
                $format,
                'text',
                'FR',
                new DateTime(),
                (new DateTime())->modify('-100 day'),
                (new DateTime())->modify('-1 day')
            );

            $parser = new CfonbParser();
            switch ($format) {
                case 'camt.xxx.cfonb120.stm':
                    $statements = $parser->read120C($fdl->getData());
                    self::assertNotEmpty($statements);
                    break;
                case 'camt.xxx.cfonb240.act':
                    $statements = $parser->read240C($fdl->getData());
                    self::assertNotEmpty($statements);
                    break;
            }

            $responseHandler = new ResponseHandlerV2();
            $code = $responseHandler->retrieveH00XReturnCode($fdl->getTransaction()->getLastSegment()->getResponse());
            $reportText = $responseHandler->retrieveH00XReportText($fdl->getTransaction()->getLastSegment()->getResponse());
            $this->assertResponseOk($code, $reportText);

            $code = $responseHandler->retrieveH00XReturnCode($fdl->getTransaction()->getReceipt());
            $reportText = $responseHandler->retrieveH00XReportText($fdl->getTransaction()->getReceipt());

            $this->assertResponseDone($code, $reportText);
        }
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group CCT
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testCCT(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['CCT']['fake']);

        $this->assertExceptionCode($codes['CCT']['code']);

        $builder = new CustomerCreditTransferBuilder();
        $customerCreditTransfer = $builder
            ->createInstance('ZKBKCHZZ80A', 'SE7500800000000000001123', 'Debitor Name')
            ->addTransaction(
                'MARKDEF1820',
                'DE09820000000083001503',
                'Creditor Name 1',
                100.10,
                'EUR',
                'Test payment  1'
            )
            ->addTransaction(
                'GIBASKBX',
                'SK4209000000000331819272',
                'Creditor Name 2',
                200.02,
                'EUR',
                'Test payment  2'
            )
            ->popInstance();

        $cct = $client->CCT($customerCreditTransfer);

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($cct->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cct->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($cct->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cct->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group CIP
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testCIP(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['CIP']['fake']);

        $this->assertExceptionCode($codes['CIP']['code']);

        $builder = new CustomerDirectDebitBuilder();
        $customerDirectDebit = $builder
            ->createInstance('ZKBKCHZZ80A', 'SE7500800000000000001123', 'Creditor Name')
            ->addTransaction('MARKDEF1820', 'DE09820000000083001503', 'Debitor Name 1', 100.10, 'EUR',
                'Test payment  1')
            ->addTransaction('GIBASKBX', 'SK4209000000000331819272', 'Debitor Name 2', 200.02, 'EUR', 'Test payment  2')
            ->popInstance();

        $cip = $client->CIP($customerDirectDebit);

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($cip->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cip->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($cip->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cip->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group XE2
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testXE2(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['XE2']['fake']);

        $this->assertExceptionCode($codes['XE2']['code']);

        $builder = new CustomerSwissCreditTransferBuilder();
        $customerCreditTransfer = $builder
            ->createInstance('ZKBKCHZZ80A', 'SE7500800000000000001123', 'Debitor Name')
            ->addBankTransaction(
                'MARKDEF1820',
                'DE09820000000083001503',
                new StructuredPostalAddress('CH', 'Triesen', '9495'),
                100.10,
                'CHF',
                'Test payment  1'
            )
            ->addSEPATransaction(
                'GIBASKBX',
                'SK4209000000000331819272',
                'Creditor Name 4',
                null, // new UnstructuredPostalAddress(),
                200.02,
                'EUR',
                'Test payment  2'
            )
            ->addForeignTransaction(
                'NWBKGB2L',
                'GB29 NWBK 6016 1331 9268 19',
                'United Development Ltd',
                new UnstructuredPostalAddress('GB', 'George Street', 'BA1 2FJ Bath'),
                65.10,
                'GBP',
                'Test payment 3'
            )
            ->popInstance();

        $xe2 = $client->XE2($customerCreditTransfer);

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($xe2->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xe2->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($xe2->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($xe2->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * @dataProvider serversDataProvider
     *
     * @group CDD
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testCDD(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['CDD']['fake']);

        $this->assertExceptionCode($codes['CDD']['code']);

        $builder = new CustomerDirectDebitBuilder();
        $customerDirectDebit = $builder
            ->createInstance('ZKBKCHZZ80A', 'SE7500800000000000001123', 'Creditor Name')
            ->addTransaction('MARKDEF1820', 'DE09820000000083001503', 'Debitor Name 1', 100.10, 'EUR',
                'Test payment  1')
            ->addTransaction('GIBASKBX', 'SK4209000000000331819272', 'Debitor Name 2', 200.02, 'EUR', 'Test payment  2')
            ->popInstance();

        $cdd = $client->CDD($customerDirectDebit);

        $responseHandler = new ResponseHandlerV2();
        $code = $responseHandler->retrieveH00XReturnCode($cdd->getTransaction()->getLastSegment()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cdd->getTransaction()->getLastSegment()->getResponse());
        $this->assertResponseOk($code, $reportText);

        $code = $responseHandler->retrieveH00XReturnCode($cdd->getTransaction()->getInitialization()->getResponse());
        $reportText = $responseHandler->retrieveH00XReportText($cdd->getTransaction()->getInitialization()->getResponse());

        $this->assertResponseOk($code, $reportText);
    }

    /**
     * Provider for servers.
     */
    public function serversDataProvider()
    {
        return [
            [
                1, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'BTD' => ['code' => '091005', 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090003', 'fake' => false],
                    'STA' => ['code' => '090003', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false],
                    'C52' => ['code' => '090003', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'C54' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                    'CIP' => ['code' => '091005', 'fake' => false],
                ],
            ],
//            [
//                2, // Credentials Id.
//                [
//                    'HEV' => ['code' => null, 'fake' => false],
//                    'INI' => ['code' => null, 'fake' => false],
//                    'HIA' => ['code' => null, 'fake' => false],
//                    'BTD' => ['code' => '091005', 'fake' => false],
//                    'HPB' => ['code' => null, 'fake' => false],
//                    'HPD' => ['code' => null, 'fake' => false],
//                    'HKD' => ['code' => null, 'fake' => false],
//                    'HTD' => ['code' => null, 'fake' => false],
//                    'HAA' => ['code' => '091006', 'fake' => false],
//                    'PTK' => ['code' => '090005', 'fake' => false],
//                    'VMK' => ['code' => '061002', 'fake' => false],
//                    'STA' => ['code' => '061002', 'fake' => false],
//                    'Z53' => ['code' => '061002', 'fake' => false],
//                    'Z54' => ['code' => '061002', 'fake' => false],
//                    'C52' => ['code' => '061002', 'fake' => false],
//                    'C53' => ['code' => '061002', 'fake' => false],
//                    'C54' => ['code' => '090003', 'fake' => false],
//                    'FDL' => [
//                        'camt.xxx.cfonb120.stm' => ['code' => '091010', 'fake' => false],
//                        'camt.xxx.cfonb240.act' => ['code' => '091010', 'fake' => false],
//                    ],
//                    'CCT' => ['code' => null, 'fake' => false],
//                    'XE2' => ['code' => null, 'fake' => false],
//                    'CDD' => ['code' => null, 'fake' => false],
//                    'CIP' => ['code' => null, 'fake' => false],
//                ],
//                new WeBankX509Generator(),
//            ],
            [
                3, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'BTD' => ['code' => '091005', 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090003', 'fake' => false],
                    'STA' => ['code' => '090003', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false],
                    'C52' => ['code' => '090003', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'C54' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                    'CIP' => ['code' => '091005', 'fake' => false],
                ],
            ],
            [
                4, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'BTD' => ['code' => '091005', 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090005', 'fake' => false],
                    'STA' => ['code' => '090005', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false],
                    'C52' => ['code' => '090003', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'C54' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                    'CCT' => ['code' => '090003', 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => '090003', 'fake' => false],
                    'CIP' => ['code' => '091005', 'fake' => false],
                ],
            ],
            [
                5, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'BTD' => ['code' => '091005', 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090003', 'fake' => false],
                    'STA' => ['code' => '090003', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'Z54' => ['code' => '090005', 'fake' => false],
                    'C52' => ['code' => '090003', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'C54' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'XE2' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                    'CIP' => ['code' => '091005', 'fake' => false],
                ],
            ],
        ];
    }
}
