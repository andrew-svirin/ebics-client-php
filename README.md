# EBICS-CLIENT-PHP

[![CI](https://github.com/andrew-svirin/ebics-client-php/actions/workflows/ci.yml/badge.svg)](https://github.com/andrew-svirin/ebics-client-php/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/andrew-svirin/ebics-client-php/v/stable)](https://packagist.org/packages/andrew-svirin/ebics-client-php)
[![Total Downloads](https://img.shields.io/packagist/dt/andrew-svirin/ebics-client-php.svg)](https://packagist.org/packages/andrew-svirin/ebics-client-php)
[![License](https://poser.pugx.org/andrew-svirin/ebics-client-php/license)](https://packagist.org/packages/andrew-svirin/ebics-client-php)

<img src="https://www.ebics.org/typo3conf/ext/siz_ebicsorg_base/Resources/Public/Images/ebics-logo.png" width="300">

PHP library to communicate with a bank through EBICS protocol.  
Supported PHP versions - PHP 7.2 - PHP 8.1  
Support Ebics server versions: 2.5 (default), 3.0  

## License
andrew-svirin/ebics-client-php is licensed under the MIT License, see the LICENSE file for details

## Installation
```bash
$ composer require andrew-svirin/ebics-client-php
```
If you need to parse Cfonb 120, 240, 360 use [andrew-svirin/cfonb-php](https://github.com/andrew-svirin/cfonb-php)
If you need to parse MT942 use [andrew-svirin/mt942-php](https://github.com/andrew-svirin/mt942-php)

## Initialize client
You will need to have this information from your Bank: 
- HostID
- HostURL
- PartnerID
- UserID

```php
<?php

use AndrewSvirin\Ebics\Services\KeyRingManager;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\EbicsClient;

// Prepare `workspace` dir in the __PATH_TO_WORKSPACES_DIR__ manually.
$keyRingRealPath = __PATH_TO_WORKSPACES_DIR__ . '/workspace/keyring.json';
// Use __IS_CERTIFIED__ true for French banks, otherwise use false.
$keyRingManager = new KeyRingManager($keyRingRealPath, __PASSWORD__);
$keyRing = $keyRingManager->loadKeyRing();
$bank = new Bank(__HOST_ID__, __HOST_URL__, __EBICS_SERVER_VERSION___);
$bank->setIsCertified(__IS_CERTIFIED__);
$user = new User(__PARTNER_ID__, __USER_ID__);
$client = new EbicsClient($bank, $user, $keyRing);
```

## Make INI, HIA, HPB requests and update key ring.
```php
<?php

use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;

try {
    /* @var \AndrewSvirin\Ebics\EbicsClient $client */
    $client->INI();
    /* @var \AndrewSvirin\Ebics\Services\KeyRingManager $keyRingManager */
    /* @var \AndrewSvirin\Ebics\Models\KeyRing $keyRing */
    $keyRingManager->saveKeyRing($keyRing);
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
    $keyRingManager->saveKeyRing($keyRing);
} catch (EbicsResponseExceptionInterface $exception) {
    echo sprintf(
        "HIA request failed. EBICS Error code : %s\nMessage : %s\nMeaning : %s",
        $exception->getResponseCode(),
        $exception->getMessage(),
        $exception->getMeaning()
    );
}

try {
    $client->HPB();
    $keyRingManager->saveKeyRing($keyRing);
} catch (EbicsResponseExceptionInterface $exception) {
    echo sprintf(
        "HPB request failed. EBICS Error code : %s\nMessage : %s\nMeaning : %s",
        $exception->getResponseCode(),
        $exception->getMessage(),
        $exception->getMeaning()
    );
}
```


## Note for French Bank
If you are dealing with a french bank, you will need to create a X509 self-signed certificate.
You can achieve this by creating a class which extends the `AbstractX509Generator` (or implements the `X509GeneratorInterface` if you want a total control about the generation)

```php
<?php

namespace App\Factories\X509;

use AndrewSvirin\Ebics\Models\X509\AbstractX509Generator;

class MyCompanyX509Generator extends AbstractX509Generator
{
    public function getCertificateOptions(array $options = []) : array {
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
```
__You can see more values in the `LegacyX509Generator` class.__ 

Once your class is created, call the `X509GeneratorFactory::setGeneratorClass()` method :
```php
<?php

//...
/* @var \AndrewSvirin\Ebics\EbicsClient $client */
$client->INI();
$client->setX509Generator(new MyCompanyX509Generator);
```

## Other examples

### FDL (File Download)
````php
<?php

use AndrewSvirin\Ebics\Exceptions\NoDownloadDataAvailableException;
use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;

try {
    /* @var \AndrewSvirin\Ebics\EbicsClient $client */
    //Fetch datas from your bank
    $response = $client->FDL('camt.xxx.cfonb120.stm');
    foreach($response->getTransactions() as $transaction) {
        //Plain format (like CFONB)
        $content = $transaction->getPlainOrderData();
    
        //XML format (Like MT942)
        $xmlContent = $transaction->getOrderData();

        //Do your work with the transactions content
        //...
    }

    //Once your work finished, tell your Bank that those files are received.
    //By doing this, you will not be able to download this documents anymore
    $client->transferReceipt($response);
} catch (NoDownloadDataAvailableException $exception) {
    echo "No data to download today !";
} catch (EbicsResponseExceptionInterface $exception) {
    echo sprintf(
        "Download failed. EBICS Error code : %s\nMessage : %s\nMeaning : %s",
        $exception->getResponseCode(),
        $exception->getMessage(),
        $exception->getMeaning()
    );
}
````

More methods you can find in `tests/EbicsTest`


## Global process and interaction with Bank Department
### 1. Create and store your 3 certificates
```php
<?php

use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;

/* @var \AndrewSvirin\Ebics\EbicsClient $client */
// For French bank.
$client->setX509Generator(new MyCompanyX509Generator);

try {
    $client->INI();
    /* @var \AndrewSvirin\Ebics\Services\KeyRingManager $keyRingManager */
    /* @var \AndrewSvirin\Ebics\Models\KeyRing $keyRing */
    $keyRingManager->saveKeyRing($keyRing);
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
    $keyRingManager->saveKeyRing($keyRing);
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
    $client->getKeyRing()
);

$txt = $ebicsBankLetter->formatBankLetter($bankLetter, $ebicsBankLetter->createPdfBankLetterFormatter());
```

### 3. Wait for the bank validation and activation access.

### 4. Fetch the bank tokens
```php

try {
    /* @var \AndrewSvirin\Ebics\EbicsClient $client */
    $client->HPB();
    /* @var \AndrewSvirin\Ebics\Services\KeyRingManager $keyRingManager */
    /* @var \AndrewSvirin\Ebics\Models\KeyRing $keyRing */
    $keyRingManager->saveKeyRing($keyRing);
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
| Transaction | Description                                                                                                 |
|-------------|-------------------------------------------------------------------------------------------------------------|
| HEV         | Supported protocol version for the Bank.                                                                    |
| INI         | Send to the bank public signature of signature A005.                                                        |
| HIA         | Send to the bank public signatures of authentication (X002) and encryption (E002).                          |
| HPB         | Retrieve the Bank public signatures authentication (X002) and encryption (E002).                            |
| HPD         | Retrieve the bank server parameters.                                                                        |
| HKD         | Retrieve customer's customer and subscriber information.                                                    |
| HTD         | Retrieve subscriber's customer and subscriber information.                                                  |
| PTK         | Downloads transaction status.                                                                               |
| FDL         | Retrieve subscriber's customer and subscriber information.                                                  |
| HAA         | Downloads the interim transaction report in SWIFT format (MT942).                                           |
| VMK         | Downloads the interim transaction report in SWIFT format (MT942).                                           |
| STA         | Retrieve the bank account statement.                                                                        |
| C52         | Retrieve the bank account report in Camt.052 format.                                                        |
| C53         | Retrieve the bank account statement in Camt.053 format.                                                     |
| Z53         | Another way to retrieve the bank account statement in Camt.053 format (i.e Switzerland financial services). |
| Z54         | Retrieve a bank account statement in Camt.054 format (i.e available in Switzerland).                        |
| CCT         | Initiate the credit transfer per Single Euro Payments Area.                                                 |
| CIP         | Initiate the instant credit transfer per Single Euro Payments Area.                                         |
| XE2         | Initiate the Swiss credit transfer (i.e available in Switzerland).                                          |
| CDD         | Initiate a direct debit transaction.                                                                        |
| BTD         | Download request (FETCH request).                                                                           |

#### Unzipping EBICS response.

Some responses are sent as zipped list of files and therefore require unzipping them first to access the XML files 
content. In this example, we use PHP's ZipArchive which requires `ext-zip` extension to unzip a camt.054 response.

```php
$zip = new \ZipArchive();

/* @var \AndrewSvirin\Ebics\EbicsClient $client */
$z54 = $client->Z54();

// ... write $z54 content into a file (zip)

$inputPath = 'zip-file-path';
$outputDir = 'your-output-directory';

$zip->extractTo($outputDir, $inputPath);

```

You'll find one or many XML files in your output directory.

### 6. Make HKD request to see what order types allowed.

---

## Donation
- Wise.com https://wise.com/send/#/enterpayment?recipient=189936743
- Bitcoin: bc1qhahtnhcrpg8u93pndjglaeuy0nc0tjau9rnst5

## Development and integration Ebics for your project and other development
👉👍 Contact Andrew Svirin https://www.linkedin.com/in/andriy-svirin-0138a177/
