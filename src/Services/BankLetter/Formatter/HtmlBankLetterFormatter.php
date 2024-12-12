<?php

namespace EbicsApi\Ebics\Services\BankLetter\Formatter;

use EbicsApi\Ebics\Models\BankLetter;
use EbicsApi\Ebics\Models\SignatureBankLetter;

/**
 * Bank letter HTML formatter.
 * View pattern.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class HtmlBankLetterFormatter extends LetterFormatter
{
    /**
     * @var string
     */
    private string $style = '';

    /**
     * Set additional CSS style.
     *
     * @param string $style
     *
     * @return void
     */
    public function setStyle(string $style): void
    {
        $this->style = $style;
    }

    /**
     * @inheritDoc
     */
    public function format(BankLetter $bankLetter): string
    {
        return <<<EOF
<html>
<head>
<style>
    h1, h2, h3, h4 {
        padding: .25rem .5rem;
        color: #fff;
    }
    h1 {
        background-color: #000;
    }
    h2 {
        break-before: page;
        background-color: #333;
    }
    h3 {
        background-color: #666;
    }
    h4 {
        background-color: #999;
    }

    .section {
        break-after: page;
    }

    pre {
        break-inside: avoid;
        padding: 0 .5rem;
    }

    table {
        break-inside: avoid;
    }
    table th, table td {
        padding: .15rem .5rem;
        text-align: left;
    }
    {$this->style}
</style>
<title>{$this->translations['init_letter']}</title>
</head>
<body>
    <h1>{$this->translations['init_letter']}</h1>
    <table>
    <tbody>
        <tr>
            <th>{$this->translations['server_name']}</th>
            <td>{$this->getServerName($bankLetter)}</td>
        </tr>
        <tr>
            <th>{$this->translations['host_id']}</th>
            <td>{$bankLetter->getBank()->getHostId()}</td>
        </tr>
        <tr>
            <th>{$this->translations['partner_id']}</th>
            <td>{$bankLetter->getUser()->getPartnerId()}</td>
        </tr>
        <tr>
            <th>{$this->translations['user_id']}</th>
            <td>{$bankLetter->getUser()->getUserId()}</td>
        </tr>
    </tbody>
    </table>
    <h2>{$this->translations['init_letter']} INI</h2>
    {$this->formatSection($bankLetter->getSignatureBankLetterA())}
    <h2>{$this->translations['init_letter']} HIA</h2>
    {$this->formatSection($bankLetter->getSignatureBankLetterE())}
    {$this->formatSection($bankLetter->getSignatureBankLetterX())}
</body>
</html>
EOF;
    }

    /**
     * Format section for one certificate.
     *
     * @param SignatureBankLetter $signatureBankLetter
     *
     * @return string
     */
    private function formatSection(SignatureBankLetter $signatureBankLetter): string
    {
        return <<<EOF
<div class="section">
    <h3>{$this->getSignatureName($signatureBankLetter)}</h3>
    <h4>{$this->translations['parameters']}</h4>
    <table>
    <tbody>
        <tr>
            <th>{$this->translations['version']}</th>
            <td>{$signatureBankLetter->getVersion()}</td>
        </tr>
        <tr>
            <th>{$this->translations['date']}</th>
            <td>{$this->getCertificateCreatedAt($signatureBankLetter)}</td>
        </tr>
    </tbody>
    </table>
    {$this->formatSectionFromCertificate($signatureBankLetter)}
    <h4>{$this->translations['hash']} (SHA-256)</h4>
    <pre>{$this->formatBytes($signatureBankLetter->getKeyHash())}</pre>
</div>
EOF;
    }

    private function formatSectionFromCertificate(SignatureBankLetter $certificateBankLetter): string
    {
        if ($certificateBankLetter->isCertified()) {
            return <<<EOF
    <h4>{$this->translations['certificate']}</h4>
    <pre>{$this->formatCertificateContent($certificateBankLetter->getCertificateContent())}</pre>
EOF;
        }

        return <<<EOF
    <h4>{$this->translations['exponent']}</h4>
    <pre>{$this->formatBytes($certificateBankLetter->getExponent())}</pre>
    <h4>{$this->translations['modulus']} ({$certificateBankLetter->getModulusSize()} bits)</h4>
    <pre>{$this->formatBytes($certificateBankLetter->getModulus())}</pre>
EOF;
    }

    /**
     * Format bytes to chain of pairs for bank format.
     *
     * @param string $bytes
     *
     * @return string In upper case.
     */
    private function formatBytes(string $bytes): string
    {
        $bytes = str_replace(' ', '', $bytes);
        $bytes = strtoupper($bytes);
        $bytes = str_split($bytes, 32);
        $bytes = array_map(
            function ($bytes) {
                return implode(' ', str_split($bytes, 2));
            },
            $bytes
        );

        return implode("\n", $bytes);
    }

    /**
     * @param string $certificateContent
     *
     * @return string
     */
    protected function formatCertificateContent(string $certificateContent): string
    {
        return chunk_split(parent::formatCertificateContent($certificateContent), 64);
    }
}
