# EBICS-CLIENT-PHP
PHP library to communicate with bank through EBICS protocol.

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

More methods you can find in `tests/Unit/EbicsTest`

### Statistic
[![Build Status](https://travis-ci.org/andrew-svirin/ebics-client-php.svg?branch=master)](https://travis-ci.com/andrew-svirin/ebics-client-php)
