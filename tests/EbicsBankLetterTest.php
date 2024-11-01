<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Contracts\EbicsClientInterface;
use AndrewSvirin\Ebics\EbicsBankLetter;

/**
 * Class EbicsBankLetterTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group ebics-bank-letter
 */
class EbicsBankLetterTest extends AbstractEbicsTestCase
{
    /**
     * Prepare bank letter in txt format.
     *
     * @dataProvider clientsDataProvider
     *
     * @group prepare-bank-letter-txt
     *
     * @param EbicsClientInterface $client
     */
    public function testPrepareBankLetterTxt(EbicsClientInterface $client)
    {
        $ebicsBankLetter = new EbicsBankLetter();

        $bankLetter = $ebicsBankLetter->prepareBankLetter(
            $client->getBank(),
            $client->getUser(),
            $client->getKeyring()
        );

        $txt = $ebicsBankLetter->formatBankLetter($bankLetter, $ebicsBankLetter->createTxtBankLetterFormatter());

        self::assertIsString($txt);
    }

    /**
     * Prepare bank letter in html format.
     *
     * @dataProvider clientsDataProvider
     *
     * @group prepare-bank-letter-html
     *
     * @param EbicsClientInterface $client
     */
    public function testPrepareBankLetterHtml(EbicsClientInterface $client)
    {
        $ebicsBankLetter = new EbicsBankLetter();

        $bankLetter = $ebicsBankLetter->prepareBankLetter(
            $client->getBank(),
            $client->getUser(),
            $client->getKeyring()
        );

        $html = $ebicsBankLetter->formatBankLetter($bankLetter, $ebicsBankLetter->createHtmlBankLetterFormatter());

        self::assertIsString($html);
    }

    /**
     * Prepare bank letter in pdf format.
     *
     * @dataProvider clientsDataProvider
     *
     * @group prepare-bank-letter-pdf
     *
     * @param EbicsClientInterface $client
     */
    public function testPrepareBankLetterPdf(EbicsClientInterface $client)
    {
        $ebicsBankLetter = new EbicsBankLetter();

        $bankLetter = $ebicsBankLetter->prepareBankLetter(
            $client->getBank(),
            $client->getUser(),
            $client->getKeyring()
        );

        $pdf = $ebicsBankLetter->formatBankLetter($bankLetter, $ebicsBankLetter->createPdfBankLetterFormatter());

        self::assertIsString($pdf);
    }

    /**
     * Provider for clients.
     */
    public function clientsDataProvider()
    {
        return [
            [
                $this->setupClientV24(2),
            ],
            [
                $this->setupClientV24(3),
            ],
            [
                $this->setupClientV25(2),
            ],
            [
                $this->setupClientV25(3),
            ],
            [
                $this->setupClientV30(2),
            ],
        ];
    }
}
