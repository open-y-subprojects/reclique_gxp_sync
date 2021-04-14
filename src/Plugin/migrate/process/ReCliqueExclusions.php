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
 *   id = "reclique_exclusions"
 * )
 */
class RecliqueExclusions extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $site_timezone = \Drupal::config('system.date')->get('timezone.default');
    $start_value = $value . ' 00:00:00';
    $end_value = $value . ' 00:00:00';
    $start_value = DateTimePlus::createFromFormat('m/d/Y H:i:s', $start_value, $site_timezone)
      ->setTimezone(new \DateTimeZone('UTC'))
      ->format('Y-m-d\TH:i:s');
    $end_value = DateTimePlus::createFromFormat('m/d/Y H:i:s', $end_value, $site_timezone)
      ->modify('+1 day')
      ->setTimezone(new \DateTimeZone('UTC'))
      ->format('Y-m-d\TH:i:s');

    return [
      'value' => $start_value,
      'end_value' => $end_value,
    ];
  }

}
