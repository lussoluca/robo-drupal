#!/bin/bash

# Exit when any command fails.
set -e

if ! command -v composer &>/dev/null; then
  echo "Composer executable could not be found"
  exit
fi

DRUPAL_CORE_CONSTRAINT="${DRUPAL_CORE_CONSTRAINT:=^8.9}"
FOLDER="${FOLDER:=drupal}"

echo -e "\n\n\nClear/Create directory of install drupal for tests"
mkdir -p $FOLDER
chmod 775 -R $FOLDER
rm -Rf $FOLDER

echo -e "\n\n\nInstalling Drupal $DRUPAL_CORE_CONSTRAINT"
composer create-project drupal/recommended-project:$DRUPAL_CORE_CONSTRAINT $FOLDER
cd $FOLDER
echo -e "\n\n\nInstalling Requirements"
composer require --no-interaction \
  drupal/core-recommended:$DRUPAL_CORE_CONSTRAINT \
  drupal/core-dev:$DRUPAL_CORE_CONSTRAINT \
  drupal/core-composer-scaffold:$DRUPAL_CORE_CONSTRAINT \
  drupal/core:$DRUPAL_CORE_CONSTRAINT

echo -e "\n\n\nInstalling RoboDrupal"
composer config repositories.0 path ../../robo-drupal
composer require lucacracco/robo-drupal:dev-master

echo -e "\nUpdating dependencies"
composer update --no-interaction

echo -e "\n\n\nCopy template settings"
cp -v ../template/tpl.settings.php web/sites/default
cp -v ../template/tpl.services.yml web/sites/default

echo -e "\n\n\nScaffold and install Drupal minimal"
./vendor/bin/robo scaffold
./vendor/bin/robo install minimal

echo -e "\n\n\nRebuild cache"
./vendor/bin/robo cache-rebuild

echo -e "\n\n\nExport configurations"
./vendor/bin/robo config:export

echo -e "\n\n\nInstall Drupal from configurations"
./vendor/bin/robo install:config minimal

echo -e "\n\n\nInstall Drupal Demo Umami"
./vendor/bin/robo install demo_umami

echo -e "\n\n\nExport database"
./vendor/bin/robo database:export /tmp

# TODO: add test other commands!
#echo -e "\n\n\nInstall Drupal from database"
#./vendor/bin/robo install umami_demo
