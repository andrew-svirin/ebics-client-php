<?php

namespace EbicsApi\Ebics\Contracts\Crypt;

use EbicsApi\Ebics\Models\Crypt\ASN1;

/**
 * Crypt ASN1 representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface ASN1Interface
{
    /**
     * Load OIDs
     *
     * Load the relevant OIDs for a particular ASN.1 semantic mapping.
     *
     * @param array $oids
     *
     * @return void
     */
    public function loadOIDs(array $oids);

    /**
     * Parse BER-encoding
     *
     * Serves a similar purpose to openssl's asn1parse
     *
     * @param string $encoded Bytes.
     *
     * @return array
     */
    public function decodeBER(string $encoded);

    /**
     * ASN.1 Map
     *
     * Provides an ASN.1 semantic mapping ($mapping) from a parsed BER-encoding to a human readable format.
     *
     * "Special" mappings may be applied on a per tag-name basis via $special.
     *
     * @param array $decoded
     * @param array $mapping
     * @param array $special
     *
     * @return array|string|false|null
     */
    public function asn1map(array $decoded, array $mapping, array $special = []);

    /**
     * Load filters
     *
     * @param array $filters
     *
     * @return void
     */
    public function loadFilters(array $filters);

    /**
     * ASN.1 Encode
     *
     * DER-encodes an ASN.1 semantic mapping ($mapping).  Some libraries would probably call this function
     * an ASN.1 compiler.
     *
     * "Special" mappings can be applied via $special.
     *
     * @param string|array $source
     * @param array $mapping
     * @param array $special
     *
     * @return string
     */
    public function encodeDER($source, array $mapping, array $special = []);

    /**
     * String type conversion
     *
     * This is a lazy conversion, dealing only with character size.
     * No real conversion table is used.
     *
     * @param string $in
     * @param int $from
     * @param int $to
     *
     * @return string|false
     */
    public function convert(string $in, int $from = ASN1::TYPE_UTF8_STRING, int $to = ASN1::TYPE_UTF8_STRING);

    /**
     * Get property ANYmap.
     *
     * @return array
     */
    public function getANYmap();

    /**
     * Get property stringTypeSize.
     *
     * @return array
     */
    public function getStringTypeSize();
}
