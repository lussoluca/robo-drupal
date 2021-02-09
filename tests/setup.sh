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
#SIMPLETEST_DB=sqlite://web/sites/default/files/.sqlite
#export SIMPLETEST_DB

echo -e "\n\n\nInstalling composer"
cp composer.dev.json drupal/composer.json
cd drupal
composer require --dev --no-interaction composer/installers:^1
# Accept a constraint for Drupal core version.
composer require --dev --no-update drupal/core-recommended:$DRUPAL_CORE_CONSTRAINT drupal/core-dev:$DRUPAL_CORE_CONSTRAINT drupal/core-composer-scaffold:$DRUPAL_CORE_CONSTRAINT drupal/core:$DRUPAL_CORE_CONSTRAINT
echo -e "\nInstalling dependencies"
composer update --dev --no-interaction
