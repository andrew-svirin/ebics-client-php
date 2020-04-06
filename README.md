# EBICS-CLIENT-PHP
PHP library to communicate with bank through EBICS protocol.
Supported PHP versions - PHP 7.2 & PHP 7.3 & PHP 7.4

### Installation
```bash
$ composer require andrew-svirin/ebics-client-php
```

### License
andrew-svirin/ebics-client-php is licensed under the MIT License, see the LICENSE file for details

### Add usage section:
```php
    use AndrewSvirin\Ebics\EbicsClient;
    use AndrewSvirin\Ebics\Handlers\ResponseHandler;
    use AndrewSvirin\Ebics\Models\Bank;
    use AndrewSvirin\Ebics\Models\User;
    use AndrewSvirin\Ebics\Services\KeyRingManager;
```

### Initialize client
```php
    // Prepare `workspace` dir in the __PATH_TO_WORKSPACES_DIR__ manually.
    $keyRingRealPath = __PATH_TO_WORKSPACES_DIR__ . '/workspace/keyring.json';
    // Use __IS_CERTIFIED__ true for French banks, otherwise use false.
    $keyRingManager = new KeyRingManager($keyRingRealPath, __PASSWORD__, __IS_CERTIFIED__);
    $keyRing = $keyRingManager->loadKeyRing();
    $bank = new Bank(__HOST_ID__, __HOST_URL__);
    $user = new User(__PARTNER_ID__, __USER_ID__);
    $client = new EbicsClient($bank, $user, $keyRing);
```

### Make INI, STA, HPB requests and update key ring.
```php
    $ini = $client->INI();
    $keyRingManager->saveKeyRing($keyRing);

    $responseHandler = new ResponseHandler();
    $code = $responseHandler->retrieveH004ReturnCode($ini);
    $reportText = $responseHandler->retrieveH004ReportText($ini);

    echo $code . '-' . $reportText . "\n";

    $hia = $client->HIA();
    $keyRingManager->saveKeyRing($keyRing);
    $code = $responseHandler->retrieveH004ReturnCode($hia);
    $reportText = $responseHandler->retrieveH004ReportText($hia);

    echo $code . '-' . $reportText . "\n";

    $hpb = $client->HPB();
    $keyRingManager->saveKeyRing($keyRing);

    $code = $responseHandler->retrieveH004ReturnCode($hia);
    $reportText = $responseHandler->retrieveH004ReportText($hia);

    echo $code . '-' . $reportText . "\n";
```


### French Bank
When using french bank, you will need to create a X509 certificate. Create a class which extends the `AbstractX509Generator` (or implements the `X509GeneratorInterface` if you want a total control about the generation)
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
You can see more values in the `LegacyX509Generator` class. 

Then call the `X509GeneratorFactory::setGeneratorClass()` method :
```php
    X509GeneratorFactory::setGeneratorClass(MyCompanyX509Generator::class);
    //...
    $client->INI();
```




More methods you can find in `tests/EbicsTest`

### Statistic
[![Build Status](https://travis-ci.org/andrew-svirin/ebics-client-php.svg?branch=master)](https://travis-ci.org/andrew-svirin/ebics-client-php)
