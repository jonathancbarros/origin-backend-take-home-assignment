#!/bin/bash

echo Uploading Application container
docker-compose up --build -d

echo Install dependencies
docker run --rm --interactive --tty -v $PWD/risk-analyzer:/app composer install

echo Information of new containers
docker ps
