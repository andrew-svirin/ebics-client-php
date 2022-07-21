<?php

namespace AndrewSvirin\Ebics\Services\BankLetter\Formatter;

use AndrewSvirin\Ebics\Contracts\BankLetter\FormatterInterface;
use AndrewSvirin\Ebics\Models\BankLetter;
use AndrewSvirin\Ebics\Models\SignatureBankLetter;
use LogicException;

/**
 * Bank letter PDF formatter.
 * View pattern.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class HtmlBankLetterFormatter implements FormatterInterface
{
    /**
     * @var array
     */
    private $translations;
    /**
     * @var string
     */
    private $style;

    public function __construct(array $translations = [], string $style = '')
    {
        $this->translations = array_replace(
            [
                'init_letter' => 'Initialization letter',
                'parameters' => 'Parameters',
                'server_name' => 'Server Name',
                'host_id' => 'Host ID',
                'partner_id' => 'Partner ID',
                'user_id' => 'User ID',
                'version' => 'Version',
                'date' => 'Date',
                'auth_certificate' => 'Authentication Certificate',
                'certificate' => 'Certificate',
                'hash' => 'Hash',
                'es_signature' => 'ES signature',
                'encryption_signature' => 'Encryption signature',
                'authentication_signature' => 'Authentication signature',
                'exponent' => 'Exponent',
                'modulus' => 'Modulus',
            ],
            $translations
        );
        $this->style = $style;
    }

    public function format(BankLetter $bankLetter): string
    {
        if (empty($serverName = $bankLetter->getBank()->getServerName())) {
            $serverName = '--';
        }

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
            <td>{$serverName}</td>
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
        switch ($signatureBankLetter->getType()) {
            case SignatureBankLetter::TYPE_A:
                $signatureName = $this->translations['es_signature'];
                break;
            case SignatureBankLetter::TYPE_E:
                $signatureName = $this->translations['encryption_signature'];
                break;
            case SignatureBankLetter::TYPE_X:
                $signatureName = $this->translations['authentication_signature'];
                break;
            default:
                throw new LogicException('Signature type unpredictable.');
        }

        if (($certificateCreatedAt = $signatureBankLetter->getCertificateCreatedAt())) {
            $createdAt = $certificateCreatedAt->format('d/m/Y H:i:s');
        } else {
            $createdAt = '--';
        }

        return <<<EOF
<div class="section">
    <h3>{$signatureName}</h3>
    <h4>{$this->translations['parameters']}</h4>
    <table>
    <tbody>
        <tr>
            <th>{$this->translations['version']}</th>
            <td>{$signatureBankLetter->getVersion()}</td>
        </tr>
        <tr>
            <th>{$this->translations['date']}</th>
            <td>{$createdAt}</td>
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
        $bytes = str_split($bytes, 16);
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
    private function formatCertificateContent(string $certificateContent): string
    {
        $result = trim(
            str_replace(
                ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"],
                '',
                $certificateContent
            )
        );

        return chunk_split($result, 64);
    }
}
