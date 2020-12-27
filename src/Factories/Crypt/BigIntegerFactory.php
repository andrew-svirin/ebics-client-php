<?php

namespace AndrewSvirin\Ebics\Factories\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\BigIntegerInterface;
use AndrewSvirin\Ebics\Models\Crypt\BigInteger;

/**
 * Class BigIntegerFactory represents producers for the @see \AndrewSvirin\Ebics\Contracts\Crypt\BigIntegerInterface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BigIntegerFactory
{

    /**
     * @param int|string|resource $x base-10 number or base-$base number if $base set.
     * @param int $base
     *
     * @return BigIntegerInterface
     */
    public function create($x = 0, int $base = 10): BigIntegerInterface
    {
        return new BigInteger($x, $base);
    }

    /**
     * Cast Big integer from phpseclib.
     *
     * @param \phpseclib\Math\BigInteger $bigInteger
     *
     * @return BigIntegerInterface
     */
    public static function createFromPhpSecLib(\phpseclib\Math\BigInteger $bigInteger): BigIntegerInterface
    {
        $obj = new BigInteger();
        foreach (get_object_vars($bigInteger) as $key => $name) {
            $obj->$key = $name;
        }
        return $obj;
    }
}
