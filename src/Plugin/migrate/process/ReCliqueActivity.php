<?php

namespace Drupal\reclique_gxp_sync\Plugin\migrate\process;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Convert ReClique Activity name to drupal node ID and revision ID.
 *
 * @MigrateProcessPlugin(
 *   id = "reclique_activity"
 * )
 */
class ReCliqueActivity extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

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
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelInterface $logger,
    ConfigFactoryInterface $configFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->config = $configFactory->get('reclique_gxp_sync.settings');
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
      $container->get('logger.factory')->get('reclique_gxp_sync'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $source = $row->getSource();

    $activityName = $source['activity'];

    $activityId = FALSE;

    // Load Program Subcategory ID (used for reference, typically "Group Exercise Classes").
    if ($subcategoryId = $this->config->get('program_subcategory')) {

      // Load categories mapping data if map has been provided.
      $category_mapping_parsed = [];
      if ($category_mapping = $this->config->get('category_mapping')) {
        $category_mapping = explode(PHP_EOL, $category_mapping);
        foreach ($category_mapping as $item) {
          $item_parsed = explode(',', $item);
          if (isset($item_parsed[0]) && isset($item_parsed[1])) {
            $category_mapping_parsed[$item_parsed[0]] = $item_parsed[1];
          }
        }
      }
      // Rename category if map has ben provided.
      $activityName = isset($category_mapping_parsed[$activityName]) ? $category_mapping_parsed[$activityName] : $activityName;

      $activityId = $this->getActivity($activityName, $subcategoryId);
    }

    if (!$activityId) {
      $message = $this->t('ReClique Migration: value="@value", title="@title", type="@type", category="@category"', [
        '@value' => $value,
        '@title' => $row->getSourceProperty('title'),
        '@type' => $row->getRawDestination()['type'],
        '@category' => $activityName,
      ]);
      $migrate_executable->saveMessage($message, MigrationInterface::MESSAGE_WARNING);
      return NULL;
    }

    return [
      'target_id' => $activityId,
      'target_revision_id' => $activityId,
    ];
  }


  /**
   * Get existing activity id.
   *
   * @param string $activity_name
   *   Activity title.
   * @param int $subcategoryId
   *   Program Subcategory node id.
   *
   * @return int|bool
   *   Activity node id.
   */
  private function getActivity($activity_name, $subcategoryId) {
    $result = $this->nodeStorage->getQuery()
      ->condition('title', $activity_name)
      ->condition('type', 'activity')
      ->condition('field_activity_category.target_id', $subcategoryId)
      ->execute();

    // Make sure there is no duplicates.
    if (count($result) > 1) {
      $msg = "Found duplicated activity name %s within subcategory %s.";
    }

    // Return node id.
    if (count($result) === 1) {
      $id = array_shift($result);
      return $id;
    }

    $this->logger->info($msg);
    return FALSE;
  }

}
