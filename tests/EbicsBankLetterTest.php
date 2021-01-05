<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\EbicsBankLetter;
use AndrewSvirin\Ebics\Services\BankLetterFormatter\BankLetterFormatterHtml;
use AndrewSvirin\Ebics\Services\BankLetterFormatter\BankLetterFormatterPdf;
use AndrewSvirin\Ebics\Services\BankLetterFormatter\BankLetterFormatterTxt;

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
     * @dataProvider serversDataProvider
     *
     * @group prepare-bank-letter-txt
     *
     * @param int $credentialsId
     */
    public function testPrepareBankLetterTxt(int $credentialsId)
    {
        $client = $this->setupClient($credentialsId);
        $ebicsBankLetter = new EbicsBankLetter();

        $bankLetter = $ebicsBankLetter->prepareBankLetter(
            $client->getBank(),
            $client->getUser(),
            $client->getKeyRing()
        );

        $txt = $ebicsBankLetter->formatBankLetter($bankLetter, new BankLetterFormatterTxt());

        $this->assertIsString($txt);
    }

    /**
     * Prepare bank letter in html format.
     *
     * @dataProvider serversDataProvider
     *
     * @group prepare-bank-letter-html
     *
     * @param int $credentialsId
     */
    public function testPrepareBankLetterHtml(int $credentialsId)
    {
        $client = $this->setupClient($credentialsId);
        $ebicsBankLetter = new EbicsBankLetter();

        $bankLetter = $ebicsBankLetter->prepareBankLetter(
            $client->getBank(),
            $client->getUser(),
            $client->getKeyRing()
        );

        $html = $ebicsBankLetter->formatBankLetter($bankLetter, new BankLetterFormatterHtml());

        $this->assertIsString($html);
    }

    /**
     * Prepare bank letter in pdf format.
     *
     * @dataProvider serversDataProvider
     *
     * @group prepare-bank-letter-pdf
     *
     * @param int $credentialsId
     */
    public function testPrepareBankLetterPdf(int $credentialsId)
    {
        $client = $this->setupClient($credentialsId);
        $ebicsBankLetter = new EbicsBankLetter();

        $bankLetter = $ebicsBankLetter->prepareBankLetter(
            $client->getBank(),
            $client->getUser(),
            $client->getKeyRing()
        );

        $pdf = $ebicsBankLetter->formatBankLetter($bankLetter, new BankLetterFormatterPdf());

        $this->assertIsString($pdf);
    }

    /**
     * Provider for servers.
     */
    public function serversDataProvider()
    {
        return [
//            [
//                1, // Credentials Id.
//            ],
            [
                2, // Credentials Id.
            ],
            [
                3, // Credentials Id.
            ],
        ];
    }
}
