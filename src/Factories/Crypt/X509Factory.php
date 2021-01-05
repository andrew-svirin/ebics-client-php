<?php

namespace AndrewSvirin\Ebics\Factories\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\X509Interface;
use AndrewSvirin\Ebics\Models\Crypt\X509;

/**
 * Class X509Factory represents producers for the @see X509.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class X509Factory
{

    /**
     * @return X509Interface
     */
    public function create(): X509Interface
    {
        return new X509();
    }
}
