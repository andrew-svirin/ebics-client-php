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
     *
     * @return KeyRing
     */
    public function loadKeyRing(): KeyRing;

    /**
     * Save KeyRing to file.
     *
     * @param KeyRing $keyRing
     */
    public function saveKeyRing(KeyRing $keyRing): void;
}
