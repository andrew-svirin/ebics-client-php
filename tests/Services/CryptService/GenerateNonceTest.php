<?php


namespace AndrewSvirin\Ebics\Tests\Services\CryptService;


use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

class GenerateNonceTest extends TestCase
{
    public function testOk()
    {
        $sUT = new CryptService();

        $result = $sUT->generateNonce();

        self::assertRegExp('/[A-Z0-9]{16}/', $result);
    }
}