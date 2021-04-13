<?php

namespace Drupal\reclique_gxp_sync\Plugin\migrate\process;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Process plugin for register link generation.
 *
 * @MigrateProcessPlugin(
 *   id = "reclique_reg_link"
 * )
 */
class ReCliqueRegLink extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($value == '1') {
      $source = $row->getSource();
      if ($reg_link_url = \Drupal::configFactory()->get('reclique_gxp_sync.settings')->get('class_sign_up_url')) {
        $reg_link_url = preg_replace('/CLASSID_PLACEHOLDER/', $source['id'], $reg_link_url);
        return $reg_link_url;
      }
    }
    return NULL;
  }

}
