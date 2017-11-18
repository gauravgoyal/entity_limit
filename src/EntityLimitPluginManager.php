<?php

namespace Drupal\entity_limit;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Plugin\Factory\DefaultFactory;

/**
 * EntityLimitPluginManager for entity limit plugins.
 */
class EntityLimitPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/EntityLimit', $namespaces, $module_handler, 'Drupal\entity_limit\Plugin\EntityLimitPluginInterface', 'Drupal\entity_limit\Annotation\EntityLimit');
    $this->alterInfo('entity_limit_info');
    $this->setCacheBackend($cache_backend, 'entity_limit_info_plugins');
  }

}
