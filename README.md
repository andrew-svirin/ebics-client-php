#EBICS-PHP
PHP library to communicate with bank through EBICS protocol.

###Instalation:
2. Include current library into vendors
```
    "repositories": [
        {
            "type": "git",
            "url": "git@bitbucket.org:ukrinsoft/ebics-php.git"
        },
        {
            "type": "git",
            "url": "git@bitbucket.org:ukrinsoft/mt942-php.git"
        }
    ],
    "require": {
        "ukrinsoft/ebics-php": "dev-master"
    },
```

###Example:
```
use Ukrinsoft\Ebics\EbicsKeyRing;
use Ukrinsoft\Ebics\EbicsBank;
use Ukrinsoft\Ebics\EbicsUser;
use Ukrinsoft\Ebics\EbicsClient;

$keysRealPath = realpath('files/mykeys.json');
$keyring = new EbicsKeyRing($keysRealPath, 'mysecret');
$bank = new EbicsBank($keyring, 'MULTIVIA', 'https://site/ebicsweb/ebicsweb');
$user = new EbicsUser($keyring, 'PartnerID', 'UserID');
$client = new EbicsClient($bank, $user);
$vmkData = $client->VMK('2016-10-02', '2016-10-27');
```