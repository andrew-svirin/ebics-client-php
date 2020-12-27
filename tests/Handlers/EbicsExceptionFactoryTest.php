<?php

namespace AndrewSvirin\Ebics\Tests\Handlers;

use AndrewSvirin\Ebics\Exceptions\BankPubkeyUpdateRequiredException;
use AndrewSvirin\Ebics\Exceptions\EbicsResponseException;
use AndrewSvirin\Ebics\Exceptions\InternalErrorException;
use AndrewSvirin\Ebics\Factories\EbicsExceptionFactory;
use AndrewSvirin\Ebics\Tests\AbstractEbicsTestCase;

/**
 * Class EbicsExceptionFactoryTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 *
 * @group ebics-exception-factory
 */
class EbicsExceptionFactoryTest extends AbstractEbicsTestCase
{
    /**
     * @dataProvider getExceptions
     */
    public function testExceptions(string $errorCode, ?string $errorText, string $expectedExceptionClass, ?string $meaning)
    {
        try{
            EbicsExceptionFactory::buildExceptionFromCode($errorCode, $errorText);
        } catch (EbicsResponseException $exception){
            $this->assertInstanceOf($expectedExceptionClass, $exception);
            $this->assertEquals($exception->getResponseCode(), $errorCode);
            $this->assertEquals($exception->getMeaning(), $meaning);
        }

    }

    public function getExceptions()
    {
        return [
            ['061099', null, InternalErrorException::class, 'An internal error occurred when processing an EBICS request.'],
            ['091008', null, BankPubkeyUpdateRequiredException::class, 'The bank verifies the hash value sent by the user. If the hash value does not match the current public keys, the bank terminates the transaction initialization.'],
            ['099999', null, EbicsResponseException::class, null],
        ];
    }
}
