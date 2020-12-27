<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\EbicsBankLetter;
use AndrewSvirin\Ebics\Services\BankLetterFormatterTxt;

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
     * @group prepare-bank-letter
     */
    public function testPrepareBankLetter()
    {
        $client = $this->setupClient(2);
        $ebicsBankLetter = new EbicsBankLetter();

        $bankLetter = $ebicsBankLetter->prepareBankLetter(
            $client->getBank(),
            $client->getUser(),
            $client->getKeyRing()
        );

        $txt = $ebicsBankLetter->formatBankLetter($bankLetter, new BankLetterFormatterTxt());

        $this->assertIsString($txt);
    }
}
