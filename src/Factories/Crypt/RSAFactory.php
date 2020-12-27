<?php

namespace AndrewSvirin\Ebics\Factories\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\RSAInterface;
use AndrewSvirin\Ebics\Models\Crypt\RSA;

/**
 * Class RSAFactory represents producers for the @see RSA.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class RSAFactory
{

    /**
     * @return RSAInterface
     */
    public function create(): RSAInterface
    {
        return new RSA();
    }
}
