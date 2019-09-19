# EBICS-CLIENT-PHP [![Build Status](https://travis-ci.org/andrew-svirin/ebics-client-php.svg?branch=master)](https://travis-ci.com/andrew-svirin/ebics-client-php)
PHP library to communicate client with bank through EBICS protocol. Register on the bank server. Retrieve payments, account, bank information. Send new payments, files.

### Installation for PHP 7.2+
```bash
$ composer require andrew-swirin/ebics-client-php
```

### License [![MIT Licence](https://badges.frapsoft.com/os/mit/mit.png?v=103)](https://opensource.org/licenses/mit-license.php)
andrew-swirin/ebics-client-php is licensed under the MIT License, see the LICENSE file for details

### Initialize client
```
      $keyRingRealPath = __PATH_TO_WORKSPACES_DIR__ . '/workspace/keyring.json';
      $keyRingManager = new KeyRingManager($keyRingRealPath, __PASSWORD__);
      $keyRing = $this->keyRingManager->loadKeyRing();
      $bank = new Bank(__HOST_ID__, __HOST_URL__);
      $user = new User(__PARTNER_ID__, __USER_ID__);
      $client = new EbicsClient($bank, $user, $keyRing);
```

### Make INI, STA, HPB requests and update key ring.
```
      $ini = $this->client->INI();
      $keyRingManager->saveKeyRing($keyRing);

      $hia = $this->client->HIA();
      $keyRingManager->saveKeyRing($keyRing);

      $hpb = $this->client->HPB();
      $keyRingManager->saveKeyRing($keyRing);
```

More methods you can find in `tests/Unit/EbicsTest`
