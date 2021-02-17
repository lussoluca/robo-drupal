#!/bin/bash

# Exit when any command fails.
set -e

if ! command -v composer &>/dev/null; then
  echo "Composer executable could not be found"
  exit
fi

FOLDER_TESTS=${FOLDER_TESTS:='/var/www/html/robo-drupal-demo'}
DRUPAL_CORE_CONSTRAINT="${DRUPAL_CORE_CONSTRAINT:=^8.9}"

echo -e "Clear/Create directory of install drupal for tests\n"
mkdir -p $FOLDER_TESTS
chmod 775 -R $FOLDER_TESTS
rm -Rf $FOLDER_TESTS

echo -e "\nInstalling Drupal $DRUPAL_CORE_CONSTRAINT on $FOLDER_TESTS\n"
composer create-project --quiet drupal/recommended-project:$DRUPAL_CORE_CONSTRAINT $FOLDER_TESTS

echo -e "\nInstalling Requirements\n"
composer require --no-interaction --quiet --working-dir=$FOLDER_TESTS \
  drupal/core-recommended:$DRUPAL_CORE_CONSTRAINT \
  drupal/core-dev:$DRUPAL_CORE_CONSTRAINT \
  drupal/core-composer-scaffold:$DRUPAL_CORE_CONSTRAINT \
  drupal/core:$DRUPAL_CORE_CONSTRAINT

echo -e "\nInstalling RoboDrupal: set custom repository for RoboDrupal\n"
composer config --working-dir=$FOLDER_TESTS repositories.0 path "${PWD}"
composer require --no-interaction --working-dir=$FOLDER_TESTS lucacracco/robo-drupal:dev-master

echo -e "\nUpdating dependencies\n"
composer update --no-interaction --quiet --working-dir=$FOLDER_TESTS

echo -e "\n\nCopy template settings\n"
cp -v "./tests/template/tpl.settings.php" "$FOLDER_TESTS/web/sites/default/tpl.settings.php"
cp -v "./tests/template/tpl.services.yml" "$FOLDER_TESTS/web/sites/default/tpl.services.yml"

cd "$FOLDER_TESTS"

echo -e "\n\nScaffold and install Drupal minimal\n\n"
./vendor/bin/robo scaffold
./vendor/bin/robo install minimal

echo -e "\n\nScaffold and install Drupal minimal\n\n"
./vendor/bin/robo status

echo -e "\n\nRebuild cache\n\n"
./vendor/bin/robo cache-rebuild

echo -e "\n\nExport configurations\n\n"
./vendor/bin/robo config:export

echo -e "\n\nInstall Drupal from configurations\n\n"
./vendor/bin/robo install:config minimal

echo -e "\n\nUpdate configuration\n\n"
./vendor/bin/drush config-set --no-interaction system.site name "Custom name"

echo -e "\n\nDeploy\n\n"
./vendor/bin/robo deploy

echo -e "\n\nExport database\n\n"
./vendor/bin/robo database:export /tmp

#echo -e "\n\nInstall Drupal from database\n\n"
#./vendor/bin/robo install:database [dump]
