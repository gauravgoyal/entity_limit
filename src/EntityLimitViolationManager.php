<?php

namespace Drupal\entity_limit;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * EntityLimitViolationManager for limit violation plugins.
 */
class EntityLimitViolationManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/EntityLimitViolation', $namespaces, $module_handler, 'Drupal\entity_limit\Plugin\EntityLimitViolationPluginInterface', 'Drupal\entity_limit\Annotation\EntityLimitViolation');
    $this->alterInfo('entity_limit_info');
    $this->setCacheBackend($cache_backend, 'entity_limit');
  }

}
