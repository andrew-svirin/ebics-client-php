<?php

namespace AndrewSvirin\Ebics\Services;

use phpseclib\Crypt\Random;

/**
 * Random function.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class RandomService
{

    /**
     * @param int $length
     *
     * @return string
     * @see Random::string
     */
    public function string(int $length): string
    {
        return Random::string($length);
    }
}
