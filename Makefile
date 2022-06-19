docker-up u:
	cd docker && docker-compose up -d

docker-down d:
	cd docker && docker-compose down

docker-build:
	cd docker && docker-compose build --no-cache

docker-php php:
	cd docker && docker exec -it php-cli-ebics-client-php /bin/bash
