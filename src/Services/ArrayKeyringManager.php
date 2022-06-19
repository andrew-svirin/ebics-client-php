<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Models\KeyRing;
use LogicException;

/**
 * EBICS KeyRing representation manage one key ring stored in the array.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ArrayKeyringManager extends KeyRingManager
{
    /**
     * @inheritDoc
     */
    public function loadKeyRing($resource, string $passphrase): KeyRing
    {
        if (!is_array($resource)) {
            throw new LogicException('Expects array.');
        }
        if (!empty($resource)) {
            $result = $this->keyRingFactory->createKeyRingFromData($resource);
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
        if (!is_array($resource)) {
            throw new LogicException('Expects array.');
        }
        $resource = $this->keyRingFactory->buildDataFromKeyRing($keyRing);
    }
}
