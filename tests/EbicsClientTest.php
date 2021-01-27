<?php

namespace AndrewSvirin\Ebics\Tests;

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
     * @group HTD
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
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
                    $this->assertNotEmpty($statements);
                    break;
                case 'camt.xxx.cfonb240.act':
                    $statements = $parser->read240C($content);
                    $this->assertNotEmpty($statements);
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
     */
    public function testZ53(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['Z53']['fake']);

        $this->assertExceptionCode($codes['Z53']['code']);
        $z53 = $client->Z53();

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
     */
    public function testC53(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator, $codes['C53']['fake']);

        $this->assertExceptionCode($codes['C53']['code']);
        $c53 = $client->C53();

        $responseHandler = new ResponseHandler();

        $c53Receipt = $client->transferReceipt($c53);
        $code = $responseHandler->retrieveH004ReturnCode($c53Receipt);
        $reportText = $responseHandler->retrieveH004ReportText($c53Receipt);

        $this->assertResponseDone($code, $reportText);
    }

    /**
     * Provider for servers.
     */
    public function serversDataProvider()
    {
        return [
//            [
//                1, // Credentials Id.
//                [
//                    'HEV' => null,
//                    'INI' => null,
//                    'HIA' => null,
//                    'HPB' => null,
//                    'HPD' => null,
//                    'HKD' => null,
//                    'HTD' => null,
//                    'HAA' => null,
//                    'VMK' => '091005',
//                    'STA' => '091005',
//                    'Z53' => '090005',
//                    'C53' => '091005',
//                ],
//            ],
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
                    'VMK' => ['code' => '061002', 'fake' => false],
                    'STA' => ['code' => '061002', 'fake' => false],
                    'Z53' => ['code' => '061002', 'fake' => false],
                    'C53' => ['code' => '061002', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091010', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091010', 'fake' => false],
                    ],
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
                    'VMK' => ['code' => '091005', 'fake' => false],
                    'STA' => ['code' => '091005', 'fake' => false],
                    'Z53' => ['code' => '090005', 'fake' => false],
                    'C53' => ['code' => '091005', 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                ],
            ],
        ];
    }
}
