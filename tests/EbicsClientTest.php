<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Exceptions\InvalidUserOrUserStateException;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Tests\Factories\X509\WeBankX509Generator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHEV(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);
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
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testINI(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        // Check that keyring is empty and or wait on success or wait on exception.
        $userExists = $client->getKeyRing()->getUserCertificateA();
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
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHIA(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        // Check that keyring is empty and or wait on success or wait on exception.
        $bankExists = $client->getKeyRing()->getUserCertificateX();
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
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHPB(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['HPB']);

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
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHKD(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['HKD']);
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
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHTD(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['HTD']);
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
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHPD(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['HPD']);
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
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testHAA(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['HAA']);
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
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testVMK(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['VMK']);
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
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSTA(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['STA']);
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
     * @group Z53
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testZ53(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['Z53']);
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
     *
     * @throws ClientExceptionInterface
     * @throws EbicsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testC53(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClient($credentialsId, $x509Generator);

        $this->assertExceptionCode($codes['C53']);
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
                    'HEV' => null,
                    'INI' => null,
                    'HIA' => null,
                    'HPB' => null,
                    'HPD' => null,
                    'HKD' => null,
                    'HTD' => null,
                    'HAA' => '091006',
                    'VMK' => '061099',
                    'STA' => '061099',
                    'Z53' => '061099',
                    'C53' => '061099',
                ],
                new WeBankX509Generator
            ],
            [
                3, // Credentials Id.
                [
                    'HEV' => null,
                    'INI' => null,
                    'HIA' => null,
                    'HPB' => null,
                    'HPD' => null,
                    'HKD' => null,
                    'HTD' => null,
                    'HAA' => null,
                    'VMK' => '091005',
                    'STA' => '091005',
                    'Z53' => '090005',
                    'C53' => '091005',
                ],
            ],
        ];
    }
}
