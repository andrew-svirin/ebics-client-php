<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\KeyRing;

/**
 * EBICS KeyRingManager representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface KeyRingManagerInterface
{
    /**
     * Load Keyring from the saved file or create new one.
     * @param array|string $resource Array with key or filepath to key.
     * @param string $passphrase Passphrase.
     */
    public function loadKeyRing($resource, string $passphrase): KeyRing;

    /**
     * Save KeyRing to file.
     * @param KeyRing $keyRing Array with key or filepath to key.
     * @param array|string $resource Array with key or filepath to key.
     */
    public function saveKeyRing(KeyRing $keyRing, &$resource): void;
}
