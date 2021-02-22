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
     * @param int|string $x base-10 number or base-$base number if $base set.
     * @param int $base
     *
     * @return BigIntegerInterface
     */
    public function create($x = 0, int $base = 10): BigIntegerInterface
    {
        return new BigInteger($x, $base);
    }
}
