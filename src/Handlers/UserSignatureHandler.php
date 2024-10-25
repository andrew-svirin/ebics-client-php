<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\Traits\C14NTrait;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\Keyring;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Models\UserSignature;
use AndrewSvirin\Ebics\Services\CryptService;

/**
 * Class AuthSignatureHandler manage body DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
abstract class UserSignatureHandler
{
    use C14NTrait;
    use XPathTrait;

    protected User $user;
    protected Keyring $keyring;
    protected CryptService $cryptService;

    public function __construct(User $user, Keyring $keyring)
    {
        $this->user = $user;
        $this->keyring = $keyring;
        $this->cryptService = new CryptService();
    }

    /**
     * Add body and children elements to request.
     * Build signature value before added PartnerID and UserID.
     *
     * @param UserSignature $xml
     * @param string $digest
     *
     * @throws EbicsException
     */
    abstract public function handle(UserSignature $xml, string $digest): void;
}
