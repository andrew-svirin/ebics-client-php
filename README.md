# EBICS-CLIENT-PHP
[![Build Status](https://travis-ci.org/andrew-svirin/ebics-client-php.svg?branch=master)](https://travis-ci.org/andrew-svirin/ebics-client-php)
[![Latest Stable Version](https://poser.pugx.org/andrew-svirin/ebics-client-php/v/stable)](https://packagist.org/packages/andrew-svirin/ebics-client-php)
[![License](https://poser.pugx.org/andrew-svirin/ebics-client-php/license)](https://packagist.org/packages/andrew-svirin/ebics-client-php)

PHP library to communicate with bank through EBICS protocol.
Supported PHP versions - PHP 7.2 & PHP 7.3 & PHP 7.4

## License
andrew-svirin/ebics-client-php is licensed under the MIT License, see the LICENSE file for details

## Installation
```bash
$ composer require andrew-svirin/ebics-client-php
```

## Initialize client
You will need to have this informations from your Bank : 
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
$bank = new Bank(__HOST_ID__, __HOST_URL__, __IS_CERTIFIED__);
$user = new User(__PARTNER_ID__, __USER_ID__);
$client = new EbicsClient($bank, $user, $keyRing);
```

## Make INI, HIA, HPB requests and update key ring.
```php
<?php

use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;

try {
    $client->INI();
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

use AndrewSvirin\Ebics\Factories\X509\AbstractX509Generator;

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

use AndrewSvirin\Ebics\Factories\X509\X509GeneratorFactory;
use App\Factories\X509\MyCompanyX509Generator;

X509GeneratorFactory::setGeneratorClass(MyCompanyX509Generator::class);
//...
$client->INI();
```

## Other examples

### FDL (File Download)
````php
<?php

use AndrewSvirin\Ebics\Exceptions\NoDownloadDataAvailableException;
use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;

try {
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


## Global process and intraction with Bank Department
### 1. Create an store your 3 certificates
```php
<?php

use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;

$client = new EbicsClient(...);
// For Franch bank.
X509GeneratorFactory::setGeneratorClass(MyCompanyX509Generator::class);

try {
    $client->INI();
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
Not covered for now in this library. **Feel free to open a PR!**
You'll have to generate a letter with a SHA-256 hash of your 3 X509 certificates.

### 3. Wait for the bank validation

### 4. Fetch the bank tokens
```php

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

### 5. Play with other transactions!
