<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\KeyringManagerInterface;
use AndrewSvirin\Ebics\Factories\KeyringFactory;
use AndrewSvirin\Ebics\Models\Keyring;

/**
 * An EbicsKeyring instance can hold sets of private user keys and/or public
 * bank keys. Private user keys are always stored AES encrypted by the
 * specified passphrase (derivated by PBKDF2). For each key file on disk or
 * same key dictionary a singleton instance is created.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class KeyringManager implements KeyringManagerInterface
{
    protected KeyringFactory $keyringFactory;

    public function __construct()
    {
        $this->keyringFactory = new KeyringFactory();
    }

    public function createKeyring(string $version): Keyring
    {
        return new Keyring($version);
    }
}
