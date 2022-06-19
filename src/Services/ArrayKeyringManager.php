<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Models\KeyRing;

/**
 * EBICS KeyRing representation manage one key ring stored in the array.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ArrayKeyringManager extends KeyRingManager
{
    /**
     * The array with keyring
     *
     * @var array
     */
    private $keyRingData;

    /**
     * Constructor.
     *
     * @param array $keyRingData
     * @param string $passphrase
     */
    public function __construct(array $keyRingData, string $passphrase)
    {
        $this->keyRingData = $keyRingData;
        parent::__construct($passphrase);
    }

    /**
     * @inheritDoc
     */
    public function loadKeyRing(): KeyRing
    {
        if (!empty($this->keyRingData)) {
            $result = $this->keyRingFactory->createKeyRingFromData($this->keyRingData);
        } else {
            $result = new KeyRing();
        }
        $result->setPassword($this->password);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function saveKeyRing(KeyRing $keyRing): array
    {
        return $this->keyRingFactory->buildDataFromKeyRing($keyRing);
    }
}
