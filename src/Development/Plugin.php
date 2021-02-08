<?php

namespace LucaCracco\RoboDrupal\Development;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Composer plugin for handling drupal spoons.
 */
class Plugin implements PluginInterface, EventSubscriberInterface, Capable {

    /**
     * The handler object to do the real work then.
     *
     * @var \LucaCracco\RoboDrupal\Development\Handler
     */
    protected $handler;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io) {
        $this->handler = new Handler($composer, $io);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io) {
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io) {
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities(): array {
        return [
            \Composer\Plugin\Capability\CommandProvider::class => CommandProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array {
        return [
            ScriptEvents::POST_UPDATE_CMD => 'configureProject',
        ];
    }

    /**
     * @param \Composer\Installer\PackageEvent $event
     */
    public static function postPackageInstall(PackageEvent $event) {
        $handler = new Handler($event->getComposer(), $event->getIO());
        $handler->configureComposerJson();
    }

    /**
     * Post update command event callback.
     *
     * @param \Composer\Script\Event $event
     *   The event that triggered the plugin.
     */
    public function configureProject(Event $event) {
        $this->handler->configureProject();
    }

}
