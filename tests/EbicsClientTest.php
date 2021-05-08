<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Builders\CustomerCreditTransfer\CustomerCreditTransferBuilder;
use AndrewSvirin\Ebics\Builders\CustomerDirectDebit\CustomerDirectDebitBuilder;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Exceptions\InvalidUserOrUserStateException;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
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

        $responseHandler = new ResponseHandler();
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
            $responseHandler = new ResponseHandler();
            $keyRingManager = $this->setupKeyKeyRingManager($credentialsId);
            $keyRingManager->saveKeyRing($client->getKeyRing());
            $code = $responseHandler->retrieveH004ReturnCode($ini);
            $reportText = $responseHandler->retrieveH004ReportText($ini);
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
            $responseHandler = new ResponseHandler();
            $keyRingManager = $this->setupKeyKeyRingManager($credentialsId);
            $keyRingManager->saveKeyRing($client->getKeyRing());
            $code = $responseHandler->retrieveH004ReturnCode($hia);
            $reportText = $responseHandler->retrieveH004ReportText($hia);
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
        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($hpb);
        $reportText = $responseHandler->retrieveH004ReportText($hpb);
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

        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($hkd);
        $reportText = $responseHandler->retrieveH004ReportText($hkd);
        $this->assertResponseOk($code, $reportText);

        $hkdReceipt = $client->transferReceipt($hkd);
        $code = $responseHandler->retrieveH004ReturnCode($hkdReceipt);
        $reportText = $responseHandler->retrieveH004ReportText($hkdReceipt);

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

        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($ptk);
        $reportText = $responseHandler->retrieveH004ReportText($ptk);
        $this->assertResponseOk($code, $reportText);

        $ptkReceipt = $client->transferReceipt($ptk);
        $code = $responseHandler->retrieveH004ReturnCode($ptkReceipt);
        $reportText = $responseHandler->retrieveH004ReportText($ptkReceipt);

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

        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($htd);
        $reportText = $responseHandler->retrieveH004ReportText($htd);
        $this->assertResponseOk($code, $reportText);

        $htdReceipt = $client->transferReceipt($htd);
        $code = $responseHandler->retrieveH004ReturnCode($htdReceipt);
        $reportText = $responseHandler->retrieveH004ReportText($htdReceipt);

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

        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($hpd);
        $reportText = $responseHandler->retrieveH004ReportText($hpd);
        $this->assertResponseOk($code, $reportText);

        $hpdReceipt = $client->transferReceipt($hpd);
        $code = $responseHandler->retrieveH004ReturnCode($hpdReceipt);
        $reportText = $responseHandler->retrieveH004ReportText($hpdReceipt);

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

        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($haa);
        $reportText = $responseHandler->retrieveH004ReportText($haa);

        $this->assertResponseOk($code, $reportText);

        $haaReceipt = $client->transferReceipt($haa);
        $code = $responseHandler->retrieveH004ReturnCode($haaReceipt);
        $reportText = $responseHandler->retrieveH004ReportText($haaReceipt);

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

        $responseHandler = new ResponseHandler();

        $vmkReceipt = $client->transferReceipt($vmk);
        $code = $responseHandler->retrieveH004ReturnCode($vmkReceipt);
        $reportText = $responseHandler->retrieveH004ReportText($vmkReceipt);

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

        $responseHandler = new ResponseHandler();

        $staReceipt = $client->transferReceipt($sta);
        $code = $responseHandler->retrieveH004ReturnCode($staReceipt);
        $reportText = $responseHandler->retrieveH004ReportText($staReceipt);

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
                'plain',
                'FR',
                new DateTime(),
                (new DateTime())->modify('-100 day'),
                (new DateTime())->modify('-1 day')
            );

            $content = '';
            foreach ($fdl->getTransactions() as $transaction) {
                //Plain format (like CFONB)
                $content .= $transaction->getPlainOrderData();
            }

            $parser = new CfonbParser();
            switch ($format) {
                case 'camt.xxx.cfonb120.stm':
                    $statements = $parser->read120C($content);
                    self::assertNotEmpty($statements);
                    break;
                case 'camt.xxx.cfonb240.act':
                    $statements = $parser->read240C($content);
                    self::assertNotEmpty($statements);
                    break;
            }

            $fdlReceipt = $client->transferReceipt($fdl);

            $responseHandler = new ResponseHandler();
            $code = $responseHandler->retrieveH004ReturnCode($fdlReceipt);
            $reportText = $responseHandler->retrieveH004ReportText($fdlReceipt);

            $this->assertResponseDone($code, $reportText);
        }
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

        $responseHandler = new ResponseHandler();

        $z53Receipt = $client->transferReceipt($z53);
        $code = $responseHandler->retrieveH004ReturnCode($z53Receipt);
        $reportText = $responseHandler->retrieveH004ReportText($z53Receipt);

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

        $responseHandler = new ResponseHandler();

        $c53Receipt = $client->transferReceipt($c53);
        $code = $responseHandler->retrieveH004ReturnCode($c53Receipt);
        $reportText = $responseHandler->retrieveH004ReportText($c53Receipt);

        $this->assertResponseDone($code, $reportText);
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

        $cctTransfer = $client->transferTransfer($cct);

        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($cctTransfer);
        $reportText = $responseHandler->retrieveH004ReportText($cctTransfer);

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

        $cddTransfer = $client->transferTransfer($cdd);

        $responseHandler = new ResponseHandler();
        $code = $responseHandler->retrieveH004ReturnCode($cddTransfer);
        $reportText = $responseHandler->retrieveH004ReportText($cddTransfer);

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
                    'HPB' => ['code' => null, 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '091005', 'fake' => false],
                    'STA' => ['code' => '091005', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'C53' => ['code' => '091005', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                ],
            ],
            [
                2, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => '091006', 'fake' => false],
                    'PTK' => ['code' => '090005', 'fake' => false],
                    'VMK' => ['code' => '061002', 'fake' => false],
                    'STA' => ['code' => '061002', 'fake' => false],
                    'Z53' => ['code' => '061002', 'fake' => false],
                    'C53' => ['code' => '061002', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091010', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091010', 'fake' => false],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                ],
                new WeBankX509Generator(),
            ],
            [
                3, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => '091116', 'fake' => false],
                    'VMK' => ['code' => '090003', 'fake' => false],
                    'STA' => ['code' => '090003', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                    'CCT' => ['code' => null, 'fake' => false],
                    'CDD' => ['code' => null, 'fake' => false],
                ],
            ],
            [
                4, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'HPD' => ['code' => null, 'fake' => false],
                    'HKD' => ['code' => null, 'fake' => false],
                    'HTD' => ['code' => null, 'fake' => false],
                    'HAA' => ['code' => null, 'fake' => false],
                    'PTK' => ['code' => null, 'fake' => false],
                    'VMK' => ['code' => '090005', 'fake' => false],
                    'STA' => ['code' => '090005', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'C53' => ['code' => '090003', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                    'CCT' => ['code' => '090003', 'fake' => false],
                    'CDD' => ['code' => '090003', 'fake' => false],
                ],
            ],
//            [
//                5, // Credentials Id.
//                [
//                    'HEV' => ['code' => null, 'fake' => false],
//                    'INI' => ['code' => null, 'fake' => false],
//                    'HIA' => ['code' => null, 'fake' => false],
//                    'HPB' => ['code' => null, 'fake' => false],
//                    'HPD' => ['code' => null, 'fake' => false],
//                    'HKD' => ['code' => null, 'fake' => false],
//                    'HTD' => ['code' => null, 'fake' => false],
//                    'HAA' => ['code' => null, 'fake' => false],
//                    'PTK' => ['code' => null, 'fake' => false],
//                    'VMK' => ['code' => '091005', 'fake' => false],
//                    'STA' => ['code' => '091005', 'fake' => false],
//                    'Z53' => ['code' => '090005', 'fake' => false],
//                    'C53' => ['code' => '091005', 'fake' => false],
//                    'FDL' => [
//                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
//                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
//                    ],
//                    'CCT' => ['code' => null, 'fake' => false],
//                    'CDD' => ['code' => null, 'fake' => false],
//                ],
//            ],
        ];
    }
}
