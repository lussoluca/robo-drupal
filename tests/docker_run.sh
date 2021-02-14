#!/bin/bash

# Exit when any command fails.
set -e

PHP_VERSION=${PHP_VERSION:='7.2-dev'}
DRUPAL_CORE_CONSTRAINT="${DRUPAL_CORE_CONSTRAINT:=^8.9}"

echo -e "Run tests with PHP $PHP_VERSION for Drupal $DRUPAL_CORE_CONSTRAINT\n"
docker run -it --rm -v "$PWD":/var/www/html/robo-drupal -w /var/www/html \
  --env DRUPAL_CORE_CONSTRAINT=$DRUPAL_CORE_CONSTRAINT \
  wodby/drupal-php:$PHP_VERSION \
  /bin/bash -c "sudo apk update && sudo apk add sqlite \
  && cd robo-drupal \
  && ./tests/test_run.sh"