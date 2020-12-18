<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;

/**
 * EBICS bank letter prepare.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class EbicsBankLetter
{

    /**
     * Prepare variables for bank letter.
     * On this moment should be called INI and HEA.
     *
     * @return array [
     *   ''
     * ]
     */
    public function prepareLetter(Bank $bank, User $user, KeyRing $keyRing)
    {
        return [];
    }
}
