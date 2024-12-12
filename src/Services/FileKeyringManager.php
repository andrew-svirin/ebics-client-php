<?php

namespace EbicsApi\Ebics\Services;

use EbicsApi\Ebics\Models\Keyring;
use LogicException;

/**
 * EBICS Keyring representation manage one keyring stored in the file.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class FileKeyringManager extends KeyringManager
{
    /**
     * @inheritDoc
     */
    public function loadKeyring($resource, string $passphrase, string $defaultVersion = Keyring::VERSION_25): Keyring
    {
        if (!is_string($resource)) {
            throw new LogicException('Expects string.');
        }
        if (is_file($resource)
            && ($content = file_get_contents($resource))
            && is_string($content)
        ) {
            $keyring = $this->keyringFactory->createKeyringFromData(json_decode($content, true));
        } else {
            $keyring = $this->createKeyring($defaultVersion);
        }
        $keyring->setPassword($passphrase);

        return $keyring;
    }

    /**
     * @inheritDoc
     */
    public function saveKeyring(Keyring $keyring, &$resource): void
    {
        if (!is_string($resource)) {
            throw new LogicException('Expects string.');
        }
        $data = $this->keyringFactory->buildDataFromKeyring($keyring);
        file_put_contents($resource, json_encode($data, JSON_PRETTY_PRINT));
    }
}
