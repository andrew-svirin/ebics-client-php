<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\KeyRingManagerInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\KeyRingFactory;
use AndrewSvirin\Ebics\Models\KeyRing;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use function Safe\file_get_contents;
use function Safe\json_decode;
use function Safe\file_put_contents;
use function Safe\json_encode;

/**
 * EBICS KeyRing representation manage one key ring stored in the file.
 *
 * An EbicsKeyRing instance can hold sets of private user keys and/or public
 * bank keys. Private user keys are always stored AES encrypted by the
 * specified passphrase (derivated by PBKDF2). For each key file on disk or
 * same key dictionary a singleton instance is created.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class KeyRingManager implements KeyRingManagerInterface
{
    /**
     * The path to a key file.
     *
     * @var string
     */
    private $keyRingRealPath;

    /**
     * The passphrase by which all private keys are encrypted/decrypted.
     *
     * @var string
     */
    private $password;

    /**
     * Constructor.
     *
     * @param string $keyRingRealPath
     * @param string $passphrase
     */
    public function __construct($keyRingRealPath, $passphrase)
    {
        $this->keyRingRealPath = $keyRingRealPath;
        $this->password = $passphrase;
    }

    /**
     * {@inheritdoc}
     *
     * @throws JsonException
     * @throws FilesystemException
     */
    public function loadKeyRing(): KeyRing
    {
        if (is_file($this->keyRingRealPath) && ($content = file_get_contents($this->keyRingRealPath)) && !empty($content)) {
            $result = KeyRingFactory::buildKeyRingFromData(json_decode($content, true));
        } else {
            $result = new KeyRing();
        }
        $result->setPassword($this->password);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @throws JsonException
     * @throws FilesystemException
     */
    public function saveKeyRing(KeyRing $keyRing) : void
    {
        $data = KeyRingFactory::buildDataFromKeyRing($keyRing);
        file_put_contents($this->keyRingRealPath, json_encode($data, \JSON_PRETTY_PRINT));
    }
}
