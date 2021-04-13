<?php

namespace Drupal\reclique_gxp_sync\Plugin\migrate\process;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Convert ReClique location name to drupal node ID and revision ID.
 *
 * @MigrateProcessPlugin(
 *   id = "reclique_location"
 * )
 */
class ReCliqueLocation extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelInterface $logger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
      $container->get('logger.factory')->get('reclique_gxp_sync')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Check the original title.
    $return = $this->checkTitle($value);

    if (empty($return)) {
      $this->logger->notice('No Location found for: ' . $value);
    }

    return $return;
  }

  /**
   *
   */
  private function checkTitle($value) {
    // Load node by title.
    $node_ids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery('AND')
      ->condition('status', 1)
      ->condition('type', ['branch', 'camp', 'facility'], 'IN')
      ->condition('title', $value)
      ->range(0, 1)
      ->execute();

    if (empty($node_ids)) {
      return NULL;
    }

    return [
      'target_id' => reset($node_ids),
      'target_revision_id' => key($node_ids),
    ];
  }

}
