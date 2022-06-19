<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Models\KeyRing;

/**
 * EBICS KeyRing representation manage one key ring stored in the file.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class FileKeyringManager extends KeyRingManager
{
    /**
     * The path to a key file.
     *
     * @var string
     */
    private $keyRingRealPath;

    /**
     * Constructor.
     *
     * @param string $keyRingRealPath
     * @param string $passphrase
     */
    public function __construct(string $keyRingRealPath, string $passphrase)
    {
        $this->keyRingRealPath = $keyRingRealPath;
        parent::__construct($passphrase);
    }

    /**
     * @inheritDoc
     */
    public function loadKeyRing(): KeyRing
    {
        if (is_file($this->keyRingRealPath) &&
            ($content = file_get_contents($this->keyRingRealPath)) &&
            is_string($content)) {
            $result = $this->keyRingFactory->createKeyRingFromData(json_decode($content, true));
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
        $data = $this->keyRingFactory->buildDataFromKeyRing($keyRing);
        file_put_contents($this->keyRingRealPath, json_encode($data, JSON_PRETTY_PRINT));

        return $data;
    }
}
