<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Models\KeyRing;
use LogicException;

/**
 * EBICS KeyRing representation manage one key ring stored in the file.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class FileKeyringManager extends KeyRingManager
{
    /**
     * @inheritDoc
     */
    public function loadKeyRing($resource, string $passphrase): KeyRing
    {
        if (!is_string($resource)) {
            throw new LogicException('Expects string.');
        }
        if (is_file($resource) &&
            ($content = file_get_contents($resource)) &&
            is_string($content)) {
            $result = $this->keyRingFactory->createKeyRingFromData(json_decode($content, true));
        } else {
            $result = new KeyRing();
        }
        $result->setPassword($passphrase);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function saveKeyRing(KeyRing $keyRing, &$resource): void
    {
        if (!is_string($resource)) {
            throw new LogicException('Expects string.');
        }
        $data = $this->keyRingFactory->buildDataFromKeyRing($keyRing);
        file_put_contents($resource, json_encode($data, JSON_PRETTY_PRINT));
    }
}
