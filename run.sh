#!/bin/bash

echo Uploading Application container
docker-compose up --build -d

echo Installing dependencies
docker run --rm --interactive --tty -v $PWD/risk-analyzer:/app composer install

echo Running Tests
docker exec -it php /var/www/html/vendor/bin/phpunit /var/www/html/tests/
