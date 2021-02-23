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

    /**
     * Create RSA from private key.
     *
     * @param string $privateKey
     * @param string $password
     *
     * @return RSAInterface
     */
    public function createPrivate(string $privateKey, string $password): RSAInterface
    {
        $rsa = $this->create();
        $rsa->setPassword($password);
        $rsa->loadKey($privateKey, RSA::PRIVATE_FORMAT_PKCS1);

        return $rsa;
    }

    /**
     * Create RSA from public key.
     *
     * @param string|array $publicKey
     *
     * @return RSAInterface
     */
    public function createPublic($publicKey): RSAInterface
    {
        $rsa = $this->create();
        $rsa->loadKey($publicKey);
        $rsa->setPublicKey();

        return $rsa;
    }
}
