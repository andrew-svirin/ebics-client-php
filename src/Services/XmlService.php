<?php

namespace EbicsApi\Ebics\Services;

/**
 * Read xml files.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class XmlService
{
    /**
     * Read xml string and extract files content items.
     *
     * @param string $xmlContent
     *
     * @return string[]
     */
    public function extractFilesFromString(string $xmlContent): array
    {
        $files = [];

        $delimiter = '<?xml';

        while (($pos = strpos(trim($xmlContent), $delimiter, 1)) !== false && $pos > 0) {
            $files[] = trim(substr($xmlContent, 0, $pos));

            $xmlContent = trim(substr($xmlContent, $pos));
        }

        $files[] = $xmlContent;


        return $files;
    }
}
