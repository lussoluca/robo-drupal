<?php

namespace LucaCracco\RoboDrupal\Development;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use GitIgnoreWriter\GitIgnoreWriter;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Handler.
 *
 * @package SpoonsPlugin
 */
class Handler {

    const DRUPAL_SPOONS_VERSION = '1.7.0-rc1';

    /**
     * The composer object of this session.
     *
     * @var \Composer\Composer
     */
    protected $composer;

    /**
     * The input-output object of the composer session.
     *
     * @var \Composer\IO\IOInterface
     */
    protected $io;

    /**
     * Handler constructor.
     *
     * @param \Composer\Composer $composer
     *   The composer object of this session.
     * @param \Composer\IO\IOInterface $io
     *   The input-output object of the composer session.
     */
    public function __construct(Composer $composer, IOInterface $io) {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Configure composer.json.
     */
    public function configureComposerJson() {
        $projectRoot = getcwd();

        $composerJson = getenv('COMPOSER');
        if (empty($composerJson)) {
            $this->io->writeError('The environment variable COMPOSER is not set, aborting!');
            return;
        }
        $composerLock = str_replace('.json', '.lock', $composerJson);

        // Append DrupalSpoon related components to composer.json.
        $content = $this->defaultSettings();
        foreach ([$composerJson, 'composer.json'] as $file) {
            $jsonFile = new JsonFile($projectRoot . '/' . $file);
            if ($jsonFile->exists()) {
                $content = NestedArray::mergeDeep($content, $jsonFile->read());
            }
        }
        foreach ($content as $key => $value) {
            if (empty($value)) {
                unset($content[$key]);
            }
        }
        $jsonFile = new JsonFile($projectRoot . '/' . $composerJson);
        $jsonFile->write($content);

        // Add some patterns to .gitignore.
        $gitignore = new GitIgnoreWriter('.gitignore');
        foreach (['/' . $composerJson, '/' . $composerLock, '/vendor/', '/web/', '/.env', '.envrc'] as $item) {
            $gitignore->add($item);
        }
        $gitignore->save();

        // Copy .envrc.dist to project.
        $fs = new Filesystem();
        $fs->copy(dirname(__DIR__) . '/.envrc.dist', $projectRoot . '/.envrc');
    }

    /**
     * Configure Drupal project for CI and/or local tests.
     */
    public function configureProject() {
        $fs = new Filesystem();

        // Directory where the root project is being created.
        $projectRoot = getcwd();
        $full_name = $this->composer->getPackage()->getName();
        if (strpos($full_name, '/') === FALSE) {
            // We are too early, composer.json not configured yet.
            return;
        }
        [, $project_name] = explode('/', $full_name);
        $moduleRoot = $projectRoot . "/web/modules/custom/$project_name";

        // Prepare directory for current module.
        if ($fs->exists($moduleRoot)) {
            $fs->remove($moduleRoot);
        }
        $fs->mkdir($moduleRoot);
        foreach (scandir($projectRoot) as $item) {
            if (!in_array($item, ['.', '..', '.git', '.idea', 'vendor', 'web'])) {
                $rel = $fs->makePathRelative($projectRoot, $moduleRoot);
                $fs->symlink($rel . $item, $moduleRoot . "/$item");
            }
        }
    }

    /**
     * Get default settings for DrupalSpoons.
     *
     * @return array
     *   The default settings.
     */
    protected function defaultSettings(): array {
        return [
            'name' => '',
            'type' => '',
            'description' => '',
            'keywords' => [],
            'license' => 'GPL-2.0+',
            'homepage' => '',
            'authors' => [],
            'repositories' => [
                [
                    'type' => 'composer',
                    'url' => 'https://packages.drupal.org/8',
                ],
            ],
            'require' => [],
            'require-dev' => [
                'drupalspoons/composer-plugin' => 'dev-master',
                'composer/installers' => '^1',
                'drupal/core-composer-scaffold' => '^8.9',
                'cweagans/composer-patches' => '~1.0',
                'drupal/core-recommended' => '^8.9',
                'drupal/core-dev' => '^8.9',
                'drush/drush' => '^10',
                'php-parallel-lint/php-parallel-lint' => '^1.2'
            ],
            'scripts' => [
                'drush' => 'COMPOSER=composer.spoons.json vendor/bin/drush',
                'si' => 'drush si -v --db-url=${SIMPLETEST_DB:-mysql://root:password@mariadb/db}',
                'phpcs' => 'phpcs --runtime-set ignore_warnings_on_exit 1 --runtime-set ignore_errors_on_exit 1 web/modules/custom',
                'lint' => 'parallel-lint --exclude web --exclude vendor .',
                'webserver' => 'cd web && php -S 0.0.0.0:8888 .ht.router.php',
                'chromedriver' => 'chromedriver --port=9515 --verbose --whitelisted-ips --log-path=/tmp/chromedriver.log --no-sandbox',
                'unit' => 'SIMPLETEST_DB=${SIMPLETEST_DB:-mysql://root:password@mariadb/db} SIMPLETEST_BASE_URL=${SIMPLETEST_BASE_URL:-http://0.0.0.0:8888} vendor/bin/phpunit --bootstrap $PWD/web/core/tests/bootstrap.php web/modules/custom',
                'stylelint' => 'yarn --silent --cwd web/core stylelint --formatter verbose --config ./.stylelintrc.json ../modules/custom/**/*.css',
                'eslint' => 'for file in $(find -name \\*.js -not -path ./web/core/\\* -not -path ./vendor/\\*); do yarn --silent --cwd web/core eslint --no-ignore -c ./.eslintrc.json ../../$file; done',
            ],
            'config' => [
                'process-timeout' => 36000,
            ],
            'extra' => [
                'installer-paths' => [
                    'web/core' => [
                        0 => 'type:drupal-core',
                    ],
                    'web/libraries/{$name}' => [
                        0 => 'type:drupal-library',
                    ],
                    'web/modules/contrib/{$name}' => [
                        0 => 'type:drupal-module',
                    ],
                    'web/profiles/{$name}' => [
                        0 => 'type:drupal-profile',
                    ],
                    'web/themes/{$name}' => [
                        0 => 'type:drupal-theme',
                    ],
                    'drush/{$name}' => [
                        0 => 'type:drupal-drush',
                    ],
                ],
                'drupal-scaffold' => [
                    'locations' => [
                        'web-root' => 'web/',
                    ],
                ],
                'drush' => [
                    'services' => [
                        'drush.services.yml' => '^9 || ^10',
                    ],
                ],
            ],
        ];
    }

}
