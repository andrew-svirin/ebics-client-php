<?php

namespace EbicsApi\Ebics\Factories\Crypt;

use EbicsApi\Ebics\Contracts\Crypt\BigIntegerInterface;
use EbicsApi\Ebics\Models\Crypt\BigInteger;

/**
 * Class BigIntegerFactory represents producers for the @see \EbicsApi\Ebics\Contracts\Crypt\BigIntegerInterface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BigIntegerFactory
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
