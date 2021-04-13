<?php

namespace Drupal\reclique_gxp_sync\Plugin\migrate\process;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Convert ReClique Subcategory name to drupal node ID and revision ID.
 *
 * @MigrateProcessPlugin(
 *   id = "reclique_subcat"
 * )
 */
class ReCliqueSubcat extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The current migration.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, LoggerChannelInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('logger.factory')->get('reclique_gxp_sync')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Load Program Subcategory ID (used for reference).
    $program_subcategory_id = \Drupal::configFactory()->get('reclique_gxp_sync.settings')->get('program_subcategory');

    if (empty($program_subcategory_id)) {
      $message = $this->t('ReClique Migration: failed reference to Program Subcategory"');
      $migrate_executable->saveMessage($message, MigrationInterface::MESSAGE_WARNING);
      return NULL;
    }

    return [
      'target_id' => $program_subcategory_id,
      'target_revision_id' => $program_subcategory_id,
    ];
  }

}
