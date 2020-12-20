<?php

namespace AndrewSvirin\Ebics\Models\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\BigIntegerInterface;

/**
 * Crypt Big Integer model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @property BigIntegerInterface $exponent
 * @property BigIntegerInterface $modulus
 */
class BigInteger extends \phpseclib\Math\BigInteger implements BigIntegerInterface
{
}
