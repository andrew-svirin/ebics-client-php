<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Models\Keyring;
use LogicException;

/**
 * EBICS Keyring representation manage one key ring stored in the array.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ArrayKeyringManager extends KeyringManager
{
    /**
     * @inheritDoc
     */
    public function loadKeyring($resource, string $passphrase, string $defaultVersion = Keyring::VERSION_25): Keyring
    {
        if (!is_array($resource)) {
            throw new LogicException('Expects array.');
        }
        if (!empty($resource)) {
            $result = $this->keyringFactory->createKeyringFromData($resource);
        } else {
            $result = new Keyring($defaultVersion);
        }
        $result->setPassword($passphrase);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function saveKeyring(Keyring $keyring, &$resource): void
    {
        if (!is_array($resource)) {
            throw new LogicException('Expects array.');
        }
        $resource = $this->keyringFactory->buildDataFromKeyring($keyring);
    }
}
