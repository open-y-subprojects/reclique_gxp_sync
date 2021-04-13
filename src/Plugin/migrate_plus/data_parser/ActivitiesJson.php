<?php

namespace Drupal\reclique_gxp_sync\Plugin\migrate_plus\data_parser;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Prepare Activities JSON data for migration.
 *
 * @DataParser(
 *   id = "activities_json",
 *   title = @Translation("Activities JSON")
 * )
 */
class ActivitiesJson extends Json {

  /**
   * Iterator over the JSON data.
   *
   * @var \Iterator
   */
  protected $iterator;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $configFactory->get('reclique_gxp_sync.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function openSourceUrl($url) {
    // (Re)open the provided URL.
    $source_data = $this->getSourceData($url);

    // Additionally map categories (group and/or rename).
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

    // Prepare data based on source, return only unique values.
    $source_data_modified = [];
    foreach ($source_data as $row) {
      // Rename category if map has been provided.
      $classCategory = isset($category_mapping_parsed[$row['ClassCategory']]) ? $category_mapping_parsed[$row['ClassCategory']] : $row['ClassCategory'];

      $source_data_modified[$classCategory] = [
        'ID' => $row['ID'],
        'ClassCategory' => $classCategory,
        'Description' => $row['Description'],
      ];
    }
    $source_data_modified = array_values($source_data_modified);
    $this->iterator = new \ArrayIterator($source_data_modified);
    return TRUE;
  }

}
