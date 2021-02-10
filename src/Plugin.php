<?php

namespace LucaCracco\RoboDrupal;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Composer plugin for handling RoboDrupal.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * The handler object to do the real work then.
   *
   * @var \SpoonsPlugin\Handler
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
  public static function getSubscribedEvents(): array {
    return [
      ScriptEvents::POST_INSTALL_CMD => 'configureProject',
    ];
  }

  /**
   * Post install command event callback.
   *
   * @param \Composer\Script\Event $event
   *   The event that triggered the plugin.
   */
  public function configureProject(Event $event) {
    $this->handler->configureProject();
  }

}
