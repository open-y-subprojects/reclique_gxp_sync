<?php

namespace Drupal\reclique_gxp_sync\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Component\Datetime\DateTimePlus;

/**
 * Modified version of ReCliqueSchedule for iterator plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "reclique_schedule"
 * )
 */
class ReCliqueSchedule extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = $row->getSource();

    $startDate = $value['start_date'] . ' ' . $value['time_of_day'];
    $endDate = $value['end_date'] . ' ' . $value['time_of_day'];

    $site_timezone = \Drupal::config('system.date')->get('timezone.default');
    $startDate = DateTimePlus::createFromFormat('Y-m-d H:i:s', $startDate, $site_timezone)
        ->setTimezone(new \DateTimeZone('UTC'))
        ->format('Y-m-d\TH:i:s');
    $endDate = DateTimePlus::createFromFormat('Y-m-d H:i:s', $endDate, $site_timezone)
      ->modify('+' . $value['duration'] . ' minutes')
      ->setTimezone(new \DateTimeZone('UTC'))
      ->format('Y-m-d\TH:i:s');

    $days = [];
    $daysMap = [
      '1' => 'monday',
      '2' => 'tuesday',
      '3' => 'wednesday',
      '4' => 'thursday',
      '5' => 'friday',
      '6' => 'saturday',
      '7' => 'sunday',
    ];
    foreach ($value['days'] as $day) {
      $days[] = strtolower($daysMap[$day]);
    }

    $paragraph = Paragraph::create([
      'type' => 'session_time',
      'field_session_time_days' => $days,
      'field_session_time_date' => [
        'value' => $startDate,
        'end_value' => $endDate,
      ],
    ]);
    $paragraph->isNew();
    $paragraph->save();

    return [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
  }

}
