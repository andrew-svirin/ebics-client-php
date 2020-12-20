<?php

namespace AndrewSvirin\Ebics\Models\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\AESInterface;

/**
 * Crypt RSA model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class AES extends \phpseclib\Crypt\AES implements AESInterface
{

    /**
     * @see \phpseclib\Crypt\AES::MODE_CBC
     */
    const MODE_CBC = 2;

    public function setOpenSSLOptions($options)
    {
        $this->openssl_options = $options;
    }
}
