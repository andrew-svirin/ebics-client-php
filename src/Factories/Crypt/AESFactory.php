<?php

namespace AndrewSvirin\Ebics\Factories\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\AESInterface;
use AndrewSvirin\Ebics\Models\Crypt\AES;

/**
 * Class AESFactory represents producers for the @see AES.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class AESFactory
{

    /**
     * @param int $mode
     *
     * @return AESInterface
     */
    public function create($mode = AES::MODE_CBC): AESInterface
    {
        return new AES($mode);
    }
}
