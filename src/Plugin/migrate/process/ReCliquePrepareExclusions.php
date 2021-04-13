<?php

namespace Drupal\reclique_gxp_sync\Plugin\migrate\process;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Process plugin for session exclusions.
 *
 * @MigrateProcessPlugin(
 *   id = "reclique_prepare_exclusions"
 * )
 */
class ReCliquePrepareExclusions extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return [
      'start_time' => $value,
    ];
  }

}
