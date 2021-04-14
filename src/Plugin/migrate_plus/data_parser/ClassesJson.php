<?php

namespace Drupal\reclique_gxp_sync\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;

/**
 * Obtain Unique Classes JSON data for migration.
 *
 * @DataParser(
 *   id = "classes_json",
 *   title = @Translation("Classes JSON")
 * )
 */
class ClassesJson extends Json {

  /**
   * Iterator over the JSON data.
   *
   * @var \Iterator
   */
  protected $iterator;

  /**
   * {@inheritdoc}
   */
  protected function openSourceUrl($url) {
    // (Re)open the provided URL.
    $source_data = $this->getSourceData($url);

    $source_data_modified = [];
    // Prepare unique classes data from all the classes.
    foreach ($source_data as $row) {
      $source_data_modified[$row['ClassCategory']] = [
        'ID' => $row['ID'],
        'ClassCategory' => $row['ClassCategory'],
        'Description' => $row['Description'],
      ];
    }
    $source_data_modified = array_values($source_data_modified);
    $this->iterator = new \ArrayIterator($source_data_modified);
    return TRUE;
  }

}
