<?php

namespace EbicsApi\Ebics\Factories\Crypt;

use EbicsApi\Ebics\Contracts\Crypt\AESInterface;
use EbicsApi\Ebics\Models\Crypt\AES;

/**
 * Class AESFactory represents producers for the @see AES.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class AESFactory
{
    /**
     * @return AESInterface
     */
    public function create(): AESInterface
    {
        return new AES();
    }
}
