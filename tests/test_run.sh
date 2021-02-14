#!/bin/bash

# Exit when any command fails.
set -e

if ! command -v composer &>/dev/null; then
  echo "Composer executable could not be found"
  exit
fi

FOLDER_RUN_TEST=${FOLDER_RUN_TEST:='/var/www/html/robo-drupal-demo'}
FOLDER_ROBO_DRUPAL=${FOLDER_ROBO_DRUPAL:='/var/www/html/robo-drupal'}
USE_LOCAL_ROBO_DRUPAL=${USE_LOCAL_ROBO_DRUPAL}
DRUPAL_CORE_CONSTRAINT="${DRUPAL_CORE_CONSTRAINT:=^8.9}"

echo -e "Clear/Create directory of install drupal for tests\n"
mkdir -p $FOLDER_RUN_TEST
chmod 775 -R $FOLDER_RUN_TEST
rm -Rf $FOLDER_RUN_TEST

echo -e "\nInstalling Drupal $DRUPAL_CORE_CONSTRAINT\n"
composer create-project --quiet drupal/recommended-project:$DRUPAL_CORE_CONSTRAINT $FOLDER_RUN_TEST
cd $FOLDER_RUN_TEST
echo -e "\nInstalling Requirements\n"
composer require --no-interaction --quiet \
  drupal/core-recommended:$DRUPAL_CORE_CONSTRAINT \
  drupal/core-dev:$DRUPAL_CORE_CONSTRAINT \
  drupal/core-composer-scaffold:$DRUPAL_CORE_CONSTRAINT \
  drupal/core:$DRUPAL_CORE_CONSTRAINT

echo -e "\nInstalling RoboDrupal\n"
if [ -n "$USE_LOCAL_ROBO_DRUPAL" ]; then
  echo -e "\nSet custom repository for RoboDrupal\n"
  composer config repositories.0 path "$FOLDER_ROBO_DRUPAL"
fi
composer require --no-interaction --quiet lucacracco/robo-drupal:dev-master

echo -e "\nUpdating dependencies\n"
composer update --no-interaction --quiet

echo -e "\n\nCopy template settings\n"
cp -v "$FOLDER_ROBO_DRUPAL/tests/template/tpl.settings.php" "$FOLDER_RUN_TEST/web/sites/default/tpl.settings.php"
cp -v "$FOLDER_ROBO_DRUPAL/tests/template/tpl.services.yml" "$FOLDER_RUN_TEST/web/sites/default/tpl.services.yml"

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
