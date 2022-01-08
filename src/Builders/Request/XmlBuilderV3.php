<?php

namespace AndrewSvirin\Ebics\Builders\Request;

/**
 * Ebics 3.0 XmlBuilder.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class XmlBuilderV3 extends XmlBuilder
{
    public function createUnsecured(): XmlBuilder
    {
        $this->createH005(self::EBICS_UNSECURED_REQUEST);

        return $this;
    }

    public function createSecuredNoPubKeyDigests(): XmlBuilder
    {
        $this->createH005(self::EBICS_NO_PUB_KEY_DIGESTS, true);

        return $this;
    }

    public function createSecured(): XmlBuilder
    {
        $this->createH005(self::EBICS_REQUEST, true);

        return $this;
    }

    private function createH005(string $container, bool $secured = false): XmlBuilder
    {
        $this->instance = $this->dom->createElementNS('urn:org:ebics:H005', $container);
        if ($secured) {
            $this->instance->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:ds',
                'http://www.w3.org/2000/09/xmldsig#'
            );
        }
        $this->instance->setAttribute('Version', 'H005');
        $this->instance->setAttribute('Revision', '1');

        return $this;
    }
}
