<?php


namespace AndrewSvirin\Ebics\Models;


class Version
{
    const V24 = 'H003';
    const V25 = 'H004';
    const V30 = 'H005';

    public static function ns(string $version): string
    {
        $versionToNs = [
            Version::V24 => 'http://www.ebics.org/H003',
            Version::V25 => 'urn:org:ebics:H004',
            Version::V30 => 'urn:org:ebics:H005',
        ];

        if (!array_key_exists($version, $versionToNs)) {
            throw new \RuntimeException(sprintf('version "%s" not supported', $version));
        }

        return $versionToNs[$version];
    }

}