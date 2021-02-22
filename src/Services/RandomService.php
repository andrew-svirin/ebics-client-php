<?php

namespace AndrewSvirin\Ebics\Services;

/**
 * Random function.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
class RandomService
{

    /**
     * Generate random string form HEX characters in upper register.
     *
     * @param int $length
     *
     * @return string
     */
    public function hex(int $length): string
    {
        $characters = '0123456789ABCDEF';
        $randomHex = $this->random($characters, $length);

        return $randomHex;
    }

    /**
     * Generate random digits.
     *
     * @param int $length
     *
     * @return string
     */
    public function digits(int $length): string
    {
        $characters = '0123456789';
        $randomDigits = $this->random($characters, $length);

        return $randomDigits;
    }

    /**
     * Generate random characters where first character not 0.
     * @param string $characters
     * @param int $length
     *
     * @return string
     */
    private function random(string $characters, int $length)
    {
        $charactersLength = strlen($characters);

        $random = '';

        // Avoid set 0 as first character.
        $random .= $characters[rand(1, $charactersLength - 1)];

        // Generate other characters randomly.
        for ($i = 1; $i < $length; $i++) {
            $random .= $characters[rand(0, $charactersLength - 1)];
        }

        return $random;
    }
}
