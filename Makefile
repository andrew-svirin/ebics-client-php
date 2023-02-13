docker-up u:
	cd docker && docker-compose -p ebics-client-php up -d

docker-down d:
	cd docker && docker-compose -p ebics-client-php down

docker-build:
	cd docker && docker-compose -p ebics-client-php build --no-cache

docker-php php:
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php /bin/bash

check:
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php ./vendor/bin/phpcbf
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php ./vendor/bin/phpcs
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php ./vendor/bin/phpstan
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php ./vendor/bin/phpunit
