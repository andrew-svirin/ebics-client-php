<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Contexts\FULContext;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Exceptions\InvalidUserOrUserStateException;
use AndrewSvirin\Ebics\Factories\DocumentFactory;
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
class EbicsClientV24Test extends AbstractEbicsTestCase
{
    /**
     * @dataProvider serversDataProvider
     *
     * @group HEV
     * @group HEV-V24
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHEV(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClientV24($credentialsId, $x509Generator, $codes['HEV']['fake']);
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
     * @group INI-V24
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testINI(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClientV24($credentialsId, $x509Generator, $codes['INI']['fake']);

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
     * @group HIA-V24
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHIA(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClientV24($credentialsId, $x509Generator, $codes['HIA']['fake']);

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
     * Run first HIA and Activate account in bank panel.
     *
     * @dataProvider serversDataProvider
     *
     * @group HPB
     * @group HPB-V24
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testHPB(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $client = $this->setupClientV24($credentialsId, $x509Generator, $codes['HPB']['fake']);

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
     * @group FDL
     * @group FDL-V24
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
            $client = $this->setupClientV24($credentialsId, $x509Generator, $code['fake']);

            $this->assertExceptionCode($code['code']);

            $fdl = $client->FDL(
                $format,
                'text',
                'FR',
                new DateTime(),
                (new DateTime())->modify('-30 day'),
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

            $responseHandler = $client->getResponseHandler();
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
     * @group FUL
     * @group FUL-V24
     *
     * @param int $credentialsId
     * @param array $codes
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @covers
     */
    public function testFUL(int $credentialsId, array $codes, X509GeneratorInterface $x509Generator = null)
    {
        $documentFactory = new DocumentFactory();
        foreach ($codes['FUL'] as $ebcdic => $code) {
            $client = $this->setupClientV24($credentialsId, $x509Generator, $code['fake']);

            $this->assertExceptionCode($code['code']);

            $context = new FULContext();
            $context->setParameter('ebcdic', $ebcdic);

            $ful = $client->FUL(
                $code['format'],
                $documentFactory->create($code['document']),
                $context,
                new DateTime()
            );

            $responseHandler = $client->getResponseHandler();
            $code = $responseHandler->retrieveH00XReturnCode($ful->getTransaction()->getLastSegment()->getResponse());
            $reportText = $responseHandler->retrieveH00XReportText($ful->getTransaction()->getLastSegment()->getResponse());
            $this->assertResponseOk($code, $reportText);

            $code = $responseHandler->retrieveH00XReturnCode($ful->getTransaction()->getInitialization()->getResponse());
            $reportText = $responseHandler->retrieveH00XReportText($ful->getTransaction()->getInitialization()->getResponse());

            $this->assertResponseOk($code, $reportText);
        }
    }

    /**
     * Provider for servers.
     */
    public function serversDataProvider()
    {
        return [
            [
                8, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '091112', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '091112', 'fake' => false],
                    ],
                    'FUL' => [
                        'CCT' => [
                            'code' => '091112',
                            'fake' => false,
                            'document' => '<?xml version="1.0" encoding="UTF-8"?><Root></Root>',
                            'format' => 'pain.001.001.03.sct',
                        ],
                    ],
                ],
            ],
            [
                9, // Credentials Id.
                [
                    'HEV' => ['code' => null, 'fake' => false],
                    'INI' => ['code' => null, 'fake' => false],
                    'HIA' => ['code' => null, 'fake' => false],
                    'HPB' => ['code' => null, 'fake' => false],
                    'FDL' => [
                        'camt.xxx.cfonb120.stm' => ['code' => '090005', 'fake' => false],
                        'camt.xxx.cfonb240.act' => ['code' => '090005', 'fake' => false],
                    ],
                    'FUL' => [
                        'CCT' => [
                            'code' => null,
                            'fake' => false,
                            'document' => '<?xml version="1.0" encoding="UTF-8"?><Root></Root>',
                            'format' => 'pain.001.001.03.sct',
                        ],
                    ],
                ],
                new WeBankX509Generator(),
            ],
        ];
    }
}
