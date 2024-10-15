# EBICS-CLIENT-PHP

[![CI](https://github.com/andrew-svirin/ebics-client-php/actions/workflows/ci.yml/badge.svg)](https://github.com/andrew-svirin/ebics-client-php/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/andrew-svirin/ebics-client-php/v/stable)](https://packagist.org/packages/andrew-svirin/ebics-client-php)
[![Total Downloads](https://img.shields.io/packagist/dt/andrew-svirin/ebics-client-php.svg)](https://packagist.org/packages/andrew-svirin/ebics-client-php)
[![License](https://poser.pugx.org/andrew-svirin/ebics-client-php/license)](https://packagist.org/packages/andrew-svirin/ebics-client-php)

<img src="https://www.ebics.org/typo3conf/ext/siz_ebicsorg_base/Resources/Public/Images/ebics-logo.png" width="300">

PHP library to communicate with a bank through EBICS protocol.  
Supported PHP versions - PHP 7.2 - PHP 8.3  
Support Ebics server versions: 2.4 (partially), 2.5 (default), 3.0

💥 Application can be used as Standalone Service in Docker Container and be interacted with REST API - https://www.youtube.com/watch?v=wUztEHLksVM
![ebics-server-standalone.gif](doc%2Febics-server-standalone.gif)

## License

andrew-svirin/ebics-client-php is licensed under the MIT License, see the LICENSE file for details

## Development and integration Ebics for your project

👉👍 Contact Andrew Svirin https://www.linkedin.com/in/andriy-svirin-0138a177/

## Installation

```bash
$ composer require andrew-svirin/ebics-client-php
```

## Initialize client

You will need to have this information from your Bank: `HostID`, `HostURL`, `PartnerID`, `UserID`

```php
<?php

use AndrewSvirin\Ebics\Services\FileKeyringManager;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Models\X509\BankX509Generator;

// Prepare `workspace` dir in the __PATH_TO_WORKSPACES_DIR__ manually.
$keyringRealPath = __PATH_TO_WORKSPACES_DIR__ . '/workspace/keyring.json';
$keyringManager = new FileKeyringManager();
$keyring = $keyringManager->loadKeyring($keyringRealPath, __PASSWORD__, __EBICS_VERSION__);
$bank = new Bank(__HOST_ID__, __HOST_URL__);
// Use __IS_CERTIFIED__ true for EBICS 3.0 and/or French banks, otherwise use false.
if(__IS_CERTIFIED__) {
    $certificateGenerator = (new BankX509Generator());
    $certificateGenerator->setCertificateOptionsByBank($bank);
    $keyring->setCertificateGenerator($certificateGenerator);
}
$user = new User(__PARTNER_ID__, __USER_ID__);
$client = new EbicsClient($bank, $user, $keyring);
```

### Note for French Bank and for Ebics 3.0

If you are dealing with a French bank or with Ebics 3.0, you will need to create a X509 self-signed certificate. 
You can achieve this by creating a class which extends the `AbstractX509Generator`

```php
<?php

use AndrewSvirin\Ebics\Models\X509\AbstractX509Generator;

class MyCompanyX509Generator extends AbstractX509Generator
{
    protected function getCertificateOptions() : array {
        return [
             'subject' => [
                'DN' => [
                    'id-at-countryName' => 'FR',
                    'id-at-stateOrProvinceName' => 'State',
                    'id-at-localityName' => 'City',
                    'id-at-organizationName' => 'Your company',
                    'id-at-commonName' => 'yourwebsite.tld',
                    ]
                ],
                'extensions' => [
                    'id-ce-subjectAltName' => [
                    'value' => [
                        'dNSName' => '*.yourwebsite.tld',
                    ]
                ],
            ],
        ];
    }
}

$keyring->setCertificateGenerator(new MyCompanyX509Generator);
```

## Global process and interaction with Bank Department

### 1. Create and store your 3 keys

```php
<?php

use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;

/* @var \AndrewSvirin\Ebics\EbicsClient $client */

try {
    $client->INI();
    /* @var \AndrewSvirin\Ebics\Services\FileKeyringManager $keyringManager */
    /* @var \AndrewSvirin\Ebics\Models\Keyring $keyring */
    $keyringManager->saveKeyring($keyring, $keyringRealPath);
} catch (EbicsResponseExceptionInterface $exception) {
    echo sprintf(
        "INI request failed. EBICS Error code : %s\nMessage : %s\nMeaning : %s",
        $exception->getResponseCode(),
        $exception->getMessage(),
        $exception->getMeaning()
    );
}

try {
    $client->HIA();
    $keyringManager->saveKeyring($keyring, $keyringRealPath);
} catch (EbicsResponseExceptionInterface $exception) {
    echo sprintf(
        "HIA request failed. EBICS Error code : %s\nMessage : %s\nMeaning : %s",
        $exception->getResponseCode(),
        $exception->getMessage(),
        $exception->getMeaning()
    );
}
```

### 2. Generate a EBICS letter

```php
/* @var \AndrewSvirin\Ebics\EbicsClient $client */
$ebicsBankLetter = new \AndrewSvirin\Ebics\EbicsBankLetter();

$bankLetter = $ebicsBankLetter->prepareBankLetter(
    $client->getBank(),
    $client->getUser(),
    $client->getKeyring()
);

$pdf = $ebicsBankLetter->formatBankLetter($bankLetter, $ebicsBankLetter->createPdfBankLetterFormatter());
```

### 3. Wait for the bank validation and access activation.

### 4. Fetch the bank keys.

```php

try {
    /* @var \AndrewSvirin\Ebics\EbicsClient $client */
    $client->HPB();
    /* @var \AndrewSvirin\Ebics\Services\FileKeyringManager $keyringManager */
    /* @var \AndrewSvirin\Ebics\Models\Keyring $keyring */
    $keyringManager->saveKeyring($keyring, $keyringRealPath);
} catch (EbicsResponseExceptionInterface $exception) {
    echo sprintf(
        "HPB request failed. EBICS Error code : %s\nMessage : %s\nMeaning : %s",
        $exception->getResponseCode(),
        $exception->getMessage(),
        $exception->getMeaning()
    );
}
```

### 5. Play with other transactions!

| Transaction | Description                                                                                                       |
|-------------|-------------------------------------------------------------------------------------------------------------------|
| HEV         | Download supported protocol versions for the Bank.                                                                |
| INI         | Send to the bank public signature of signature A005.                                                              |
| HIA         | Send to the bank public signatures of authentication (X002) and encryption (E002).                                |
| H3K         | Send to the bank public signatures of signature (A005), authentication (X002) and encryption (E002).              |
| HPB         | Download the Bank public signatures authentication (X002) and encryption (E002).                                  |
| HPD         | Download the bank server parameters.                                                                              |
| HKD         | Download customer's customer and subscriber information.                                                          |
| HTD         | Download subscriber's customer and subscriber information.                                                        |
| PTK         | Download transaction status.                                                                                      |
| FDL         | Download the files from the bank.                                                                                 |
| FUL         | Upload the files to the bank.                                                                                     |
| HAA         | Download Bank available order types.                                                                              |
| VMK         | Download the interim transaction report in SWIFT format (MT942).                                                  |
| STA         | Download the bank account statement.                                                                              |
| C52         | Download the bank account report in Camt.052 format.                                                              |
| C53         | Download the bank account statement in Camt.053 format.                                                           |
| C54         | Download Debit Credit Notification (DTI).                                                                         |
| Z52         | Download the bank account report in Camt.052 format (i.e Switzerland financial services).                         |
| Z53         | Download the bank account statement in Camt.053 format (i.e Switzerland financial services).                      |
| Z54         | Download the bank account statement in Camt.054 format (i.e available in Switzerland).                            |
| ZSR         | Download Order/Payment Status report.                                                                             |
| XEK         | Download account information as PDF-file.                                                                         |
| CCT         | Upload initiation of the credit transfer per Single Euro Payments Area.                                           |
| CIP         | Upload initiation of the instant credit transfer per Single Euro Payments Area.                                   |
| XE2         | Upload initiation of the Swiss credit transfer (i.e available in Switzerland).                                    |
| XE3         | Upload SEPA Direct Debit Initiation, CH definitions, CORE (i.e available in Switzerland).                         |
| YCT         | Upload Credit transfer CGI (SEPA & non SEPA).                                                                     |
| CDD         | Upload initiation of the direct debit transaction.                                                                |
| CDB         | Upload initiation of the direct debit transaction for business.                                                   |
| BTD         | Download request files of any BTF structure.                                                                      |
| BTU         | Upload the files to the bank.                                                                                     |
| HVU         | Download List the orders for which the user is authorized as a signatory.                                         |
| HVZ         | Download VEU overview with additional information.                                                                |
| HVE         | Upload VEU signature for order.                                                                                   |
| HVD         | Download the state of a VEU order.                                                                                |
| HVT         | Download detailed information about an order from VEU processing for which the user is authorized as a signatory. |

If you need to parse Cfonb 120, 240, 360 use [andrew-svirin/cfonb-php](https://github.com/andrew-svirin/cfonb-php)  
If you need to parse MT942 use [andrew-svirin/mt942-php](https://github.com/andrew-svirin/mt942-php)

## Backlog
 - Format validators for all available ISO 20022 formats:
   - upload: PAIN.001, PAIN.008, MT101, TA875, CFONB320, CFONB160 with different versions.
   - download: PAIN.002, CAMT.052, CAMT.053, CAMT.054, MT199, MT900, MT910, MT940, MT942, CFONB240,CFONB245, CFONB120
 - Improve FakerHttpClient
 - Support import 3SKey certificates
 - Support change password for Keyring
