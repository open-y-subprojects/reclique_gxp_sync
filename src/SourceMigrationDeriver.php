<?php

namespace Drupal\reclique_gxp_sync;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\Derivative\DeriverInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;

/**
 * SourceMigration Deriver Class.
 */
class SourceMigrationDeriver extends DeriverBase implements DeriverInterface, ContainerDeriverInterface {

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * SourceMigrationDeriver constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get('reclique_gxp_sync.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    if ($url = $this->config->get('api_url')) {
      $base_plugin_definition['source']['urls'] = $url;
    }
    $this->derivatives[] = $base_plugin_definition;
    return $this->derivatives;
  }

}
