<?php

namespace AndrewSvirin\Ebics\Models\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\X509Interface;

/**
 * Crypt RSA model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @property \phpseclib\Math\BigInteger $exponent
 * @property \phpseclib\Math\BigInteger $modulus
 */
class X509 extends \phpseclib\File\X509 implements X509Interface
{

}
