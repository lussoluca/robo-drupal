<?php

namespace LucaCracco\RoboDrupal;

use Composer\Composer;
use Composer\IO\IOInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Handler.
 *
 * @package SpoonsPlugin
 */
class Handler {

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
   * Configure for Drupal project.
   */
  public function configureProject() {
    $this->io->write("Configure project");
    $fs = new Filesystem();

    // TODO: not yet implemented.
  }

}
