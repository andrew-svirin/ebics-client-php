# EBICS-CLIENT-PHP
PHP library to communicate with bank through EBICS protocol.

### Installation
```bash
$ composer require andrew-swirin/ebics-client-php
```

### License
andrew-swirin/ebics-client-php is licensed under the MIT License, see the LICENSE file for details

### Example
```
use AndrewSvirin\Ebics\EbicsKeyRing;
use AndrewSvirin\Ebics\EbicsBank;
use AndrewSvirin\Ebics\EbicsUser;
use AndrewSvirin\Ebics\EbicsClient;

$keysRealPath = realpath('files/mykeys.json');
$keyring = new EbicsKeyRing($keysRealPath, 'mysecret');
$bank = new EbicsBank($keyring, 'MULTIVIA', 'https://site/ebicsweb/ebicsweb');
$user = new EbicsUser($keyring, 'PartnerID', 'UserID');
$client = new EbicsClient($bank, $user);
$vmkData = $client->VMK('2016-10-02', '2016-10-27');
```

### Statistic
[![Build Status](https://travis-ci.com/andrew-svirin/ebics-client-php.svg?branch=master)](https://travis-ci.com/andrew-svirin/ebics-client-php)