<?php

namespace AndrewSvirin\Ebics\Services\BankLetter\Formatter;

use AndrewSvirin\Ebics\Contracts\BankLetter\FormatterInterface;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\BankLetter;
use AndrewSvirin\Ebics\Models\SignatureBankLetter;
use AndrewSvirin\Ebics\Models\User;
use LogicException;
use RuntimeException;

/**
 * Bank letter PDF formatter.
 * View pattern.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
class HtmlBankLetterFormatter implements FormatterInterface
{

    public function format(BankLetter $bankLetter)
    {
        $translations = [
            'init_letter' => 'Initialization letter',
        ];

        $result = <<<EOF
    <h2>{$translations['init_letter']} INI</h2>
    <hr/>
    {$this->formatSection(
            $bankLetter->getBank(),
            $bankLetter->getUser(),
            $bankLetter->getSignatureBankLetterA()
        )}
    <h2>{$translations['init_letter']} HIA</h2>
    <hr/>
    {$this->formatSection(
            $bankLetter->getBank(),
            $bankLetter->getUser(),
            $bankLetter->getSignatureBankLetterE()
        )}
    <br/><br/><br/>
    {$this->formatSection(
            $bankLetter->getBank(),
            $bankLetter->getUser(),
            $bankLetter->getSignatureBankLetterX()
        )}
EOF;

        return $result;
    }

    /**
     * Format section for one certificate.
     *
     * @param Bank $bank
     * @param User $user
     * @param SignatureBankLetter $signatureBankLetter
     *
     * @return string
     */
    private function formatSection(Bank $bank, User $user, SignatureBankLetter $signatureBankLetter): string
    {
        if ($signatureBankLetter->isCertified()) {
            $certificateSection = $this->formatSectionFromCertificate($signatureBankLetter);
        } else {
            $certificateSection = $this->formatSectionFromModulusExponent($signatureBankLetter);
        }

        $translations = [
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
        ];

        switch ($signatureBankLetter->getType()) {
            case SignatureBankLetter::TYPE_A:
                $signatureName = $translations['es_signature'];
                break;
            case SignatureBankLetter::TYPE_E:
                $signatureName = $translations['encryption_signature'];
                break;
            case SignatureBankLetter::TYPE_X:
                $signatureName = $translations['authentication_signature'];
                break;
            default:
                throw new LogicException('Signature type unpredictable.');
        }

        if (($certificateCreatedAt = $signatureBankLetter->getCertificateCreatedAt())) {
            $createdAt = $certificateCreatedAt->format('d/m/y H:i:s');
        } else {
            $createdAt = null;
        }

        $result = <<<EOF
    <h3 style="color: #FFFFFF; background-color: #333333">{$translations['parameters']}</h3>
    <table>
        <tr>
            <td><b>{$translations['server_name']}</b></td>
            <td>{$bank->getServerName()}</td>
        </tr>
        <tr>
            <td><b>{$translations['host_id']}</b></td>
            <td>{$bank->getHostId()}</td>
        </tr>
        <tr>
            <td><b>{$translations['partner_id']}</b></td>
            <td>{$user->getPartnerId()}</td>
        </tr>
        <tr>
            <td><b>{$translations['user_id']}</b></td>
            <td>{$user->getUserId()}</td>
        </tr>
        <tr>
            <td><b>{$translations['version']}</b></td>
            <td>{$signatureBankLetter->getVersion()}</td>
        </tr>
        <tr>
            <td><b>{$translations['date']}</b></td>
            <td>{$createdAt}</td>
        </tr>
    </table>
    <br/>
    <h3 style="color: #FFFFFF; background-color: #333333">{$signatureName}</h3>
    {$certificateSection}
    <br/><br/>
    <b>{$translations['hash']}</b>
    <br/>
    {$this->formatBytes($signatureBankLetter->getKeyHash())}
EOF;

        return $result;
    }

    private function formatSectionFromCertificate(SignatureBankLetter $certificateBankLetter): string
    {
        $translations = [
            'certificate' => 'Certificate',
        ];

        $result = <<<EOF
    <b>{$translations['certificate']}</b>
    <br/>
    {$this->formatCertificateContent($certificateBankLetter->getCertificateContent())}
EOF;

        return $result;
    }

    private function formatSectionFromModulusExponent(SignatureBankLetter $certificateBankLetter): string
    {
        $translations = [
            'exponent' => 'Exponent',
            'modulus' => 'Modulus',
        ];

        $result = <<<EOF
    <b>{$translations['exponent']}</b>
    <br/>
    {$this->formatBytes($certificateBankLetter->getExponent())}
    <br/>
    <b>{$translations['modulus']}</b>
    <br/>
    {$this->formatBytes($certificateBankLetter->getModulus())}
EOF;

        return $result;
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
        $result = '';
        $newLineNum = 48;
        $newLineChar = "\n";

        // Add fictive space.
        $bytes = ' ' . $bytes;
        $length = strlen($bytes);

        // Prepare result from bytes. Replace every n-character by new line.
        for ($i = 0; $i < $length; $i++) {
            $isNewLine = (0 === $i % $newLineNum);
            if ($isNewLine) {
                if (' ' !== $bytes[$i]) {
                    throw new RuntimeException('Character must be a space.');
                }
                $result[$i] = $newLineChar;
            } else {
                $result[$i] = $bytes[$i];
            }
        }

        // Convert to upper case and trim leading fictive space.
        return strtoupper(trim($result));
    }

    /**
     * @param string $certificateContent
     *
     * @return string
     */
    private function formatCertificateContent(string $certificateContent): string
    {
        $result = trim(str_replace(
            ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"],
            '',
            $certificateContent
        ));

        return $result;
    }
}
