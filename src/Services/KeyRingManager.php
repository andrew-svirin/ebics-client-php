<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\KeyRingManagerInterface;
use AndrewSvirin\Ebics\Factories\KeyRingFactory;

/**
 * An EbicsKeyRing instance can hold sets of private user keys and/or public
 * bank keys. Private user keys are always stored AES encrypted by the
 * specified passphrase (derivated by PBKDF2). For each key file on disk or
 * same key dictionary a singleton instance is created.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class KeyRingManager implements KeyRingManagerInterface
{
    /**
     * The passphrase by which all private keys are encrypted/decrypted.
     *
     * @var string
     */
    protected $password;

    /**
     * @var KeyRingFactory
     */
    protected $keyRingFactory;

    /**
     * Constructor.
     *
     * @param string $passphrase
     */
    public function __construct(string $passphrase)
    {
        $this->password = $passphrase;
        $this->keyRingFactory = new KeyRingFactory();
    }
}
