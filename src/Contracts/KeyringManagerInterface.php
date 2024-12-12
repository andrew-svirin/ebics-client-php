<?php

namespace EbicsApi\Ebics\Contracts;

use EbicsApi\Ebics\Models\Keyring;

/**
 * EBICS KeyringManager representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface KeyringManagerInterface
{
    /**
     * Load Keyring from the saved file or create new one.
     * @param array|string $resource Array with key or filepath to key.
     * @param string $passphrase Passphrase.
     * @param string $defaultVersion Default keyring version.
     */
    public function loadKeyring($resource, string $passphrase, string $defaultVersion = Keyring::VERSION_25): Keyring;

    /**
     * Save Keyring to file.
     * @param Keyring $keyring Array with key or filepath to key.
     * @param array|string $resource Array with key or filepath to key.
     */
    public function saveKeyring(Keyring $keyring, &$resource): void;
}
