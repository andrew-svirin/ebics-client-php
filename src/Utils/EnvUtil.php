<?php

namespace AndrewSvirin\Ebics\Utils;

/**
 * Manage environment variables.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class EnvUtil
{
    /**
     * Get secret.
     */
    public static function getSecret(): string
    {
        if (!($secret = getenv('SECRET'))) {
            trigger_error('Should be specified SECRET env var.');
        }

        return $secret;
    }

    /**
     * Is debug mode or not.
     */
    public static function isDebug(): bool
    {
        return \is_string(getenv('DEBUG')) && 'TRUE' === getenv('DEBUG');
    }

    /**
     * Credentials 1 details. Optional.
     *
     * @return array
     */
    public static function getCredentials1(): ?array
    {
        return \is_string(getenv('DEBUG_CREDENTIALS_1')) ? self::prepareCredentials(explode(':', getenv('DEBUG_CREDENTIALS_1'), 5)) : null;
    }

    /**
     * Credentials 2 details. Optional.
     *
     * @return array
     */
    public static function getCredentials2(): ?array
    {
        return \is_string(getenv('DEBUG_CREDENTIALS_2')) ? self::prepareCredentials(explode(':', getenv('DEBUG_CREDENTIALS_2'), 5)) : null;
    }

    /**
     * Credentials 3 details. Optional.
     *
     * @return array
     */
    public static function getCredentials3(): ?array
    {
        return \is_string(getenv('DEBUG_CREDENTIALS_3')) ? self::prepareCredentials(explode(':', getenv('DEBUG_CREDENTIALS_3'), 5)) : null;
    }

    /**
     * Associate credentials data with keys.
     */
    private static function prepareCredentials(array $data): array
    {
        return [
         'hostId' => $data[0],
         'hostURL' => 'https://' . $data[1],
         'hostIsCertified' => 'TRUE' === $data[2],
         'partnerId' => $data[3],
         'userId' => $data[4],
      ];
    }
}
