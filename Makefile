docker-up u:
	cd docker && docker-compose up -d

docker-down d:
	cd docker && docker-compose down

docker-build:
	cd docker && docker-compose build --no-cache

docker-php php:
	cd docker && docker exec -it php-cli-ebics-client-php /bin/bash

check:
	cd docker && docker exec -it php-cli-ebics-client-php ./vendor/bin/phpcbf
	cd docker && docker exec -it php-cli-ebics-client-php ./vendor/bin/phpcs
	cd docker && docker exec -it php-cli-ebics-client-php ./vendor/bin/phpstan
	cd docker && docker exec -it php-cli-ebics-client-php ./vendor/bin/phpunit
