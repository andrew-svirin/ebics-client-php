<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Models\CertificateX509;
use RuntimeException;

/**
 * Class CertificateX509Factory represents producers for the @see CertificateX509.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin, Guillaume Sainthillier
 */
final class CertificateX509Factory
{
    /**
     * @param string $content
     *
     * @return CertificateX509
     */
    public function createFromContent(string $content): CertificateX509
    {
        $certificateX509 = new CertificateX509();
        if (false === $certificateX509->loadX509($content)) {
            throw new RuntimeException('Can not load certificate X509 content.');
        }

        return $certificateX509;
    }
}
