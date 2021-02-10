#!/bin/bash

# Exit when any command fails.
set -e

if ! command -v composer &> /dev/null
then
  echo "Composer executable could not be found"
  exit
fi

echo -e "\n\n\nClear/Create directory of install drupal for tests"
mkdir -p drupal
chmod 775 -R drupal
rm -rf drupal/*

DRUPAL_CORE_CONSTRAINT="${DRUPAL_CORE_CONSTRAINT:=^8.9}"

echo -e "\n\n\nCopy composer.json for tests"
cp composer.dev.json drupal/composer.json
cd drupal

echo -e "\n\n\nInstalling composer and Drupal $DRUPAL_CORE_CONSTRAINT"
composer require --dev --no-update composer/installers:^1 drupal/core-recommended:$DRUPAL_CORE_CONSTRAINT drupal/core-dev:$DRUPAL_CORE_CONSTRAINT drupal/core-composer-scaffold:$DRUPAL_CORE_CONSTRAINT drupal/core:$DRUPAL_CORE_CONSTRAINT

echo -e "\nUpdating dependencies"
composer update --dev --no-interaction
