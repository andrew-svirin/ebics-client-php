<?php

namespace AndrewSvirin\Ebics\contracts;

use AndrewSvirin\Ebics\models\KeyRing;

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
    * @return KeyRing
    */
   function loadKeyRing(): KeyRing;

   /**
    * Save KeyRing to file.
    * @param KeyRing $keyRing
    */
   function saveKeyRing(KeyRing $keyRing);

}