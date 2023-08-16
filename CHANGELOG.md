## 2.1

* Up supported PHP version to >= 7.4
* Add `FUL`, `H3K` order type for EBICS 2.5
* Add methods `YCT`, `ZSR`, `Z54` order type for EBICS 3.0
* Major update for keyring manager. Added Array & File keyring managers instead of keyring.
* Add responseHandler into ClientInterface
* Add $storageCallback for download methods that handle acknowledge.
* Fix UTF-8 encoding.
* Added CurlHttpClient and PsrHttpClient to use standard client.
* Updated AbstractX509Generator to handle custom options.
* Improved Bank-letter
* Fixed padding for encoding that caused problems for upload methods.
* Added XE3
* Fixed CCT, CDD builder.
