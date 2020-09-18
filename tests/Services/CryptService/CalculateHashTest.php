<?php


namespace AndrewSvirin\Ebics\Tests\Services\CryptService;


use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;

class CalculateHashTest extends TestCase
{
    public function testOk()
    {
        $sUT = new CryptService();

        $result = $sUT->calculateHash('test');

        self::assertIsString($result);
        self::assertFalse(ctype_print($result)); // binary
    }
}