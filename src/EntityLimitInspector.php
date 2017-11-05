<?php

namespace Drupal\entity_limit;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Provide handler for all entity limit usage functions.
 */
class EntityLimitInspector {

  protected $entityManager;

  protected $pluginManager;

  /**
   * Construct entity_limit usage.
   *
   * @param EntityManagerInterface $entityManager
   *   Entity Manager.
   */
  public function __construct(EntityManagerInterface $entityManager, PluginManagerInterface $pluginManager) {
    $this->entityManager = $entityManager;
    $this->pluginManager = $pluginManager;
  }

  /**
   * Check whether user has crossed the entity limits.
   *
   * @param string $entity_type_id
   *   Entity type.
   * @param string $entity_bundle
   *   Bundle name.
   * @param object $account
   *   User object.
   *
   * @return bool
   *   True if limit has reached otherwise false.
   */
  public function checkEntityLimits($entity_type_id, $entity_bundle, $account = NULL) {
    $access = TRUE;
    $applicable_limits = $this->getApplicableLimits($entity_type_id, $entity_bundle);
    $available_plugins = $this->pluginManager->getDefinitions();

    // Foreach applicable limits check if account passes the criterion.
    foreach ($applicable_limits as $entity_limit) {
      $plugin_id = $entity_limit->getPlugin();
      $plugin_access = $available_plugins[$plugin_id]['class']::validateAccountLimit($account, $entity_limit);
      if (!$plugin_access) {
        $access = FALSE;
        break;
      }
    }
    return $access;
  }

  /**
   * Gets the entity limits.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   *
   * @return array
   *   The entity limits.
   */
  public function getEntityLimits($entity_type_id) {
    $configurations = [];
    $configStorage = $this->entityManager->getStorage('entity_limit');
    $configurations = $configStorage->loadByProperties([
      'entity_type' => $entity_type_id,
    ]
    );
    return $configurations;
  }

  /**
   * Gets the bundle limits.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param string $entity_bundle
   *   The entity bundle.
   *
   * @return array
   *   The bundle limits.
   */
  public function getApplicableLimits($entity_type_id, $entity_bundle) {
    $entity_limits = $this->getEntityLimits($entity_type_id);
    $applicable_limits = [];
    foreach ($entity_limits as $key => $entity_limit) {
      if (in_array($entity_bundle, $entity_limit->getEntityLimitBundles())) {
        $applicable_limits[$key] = $entity_limit;
      }
    }
    return $applicable_limits;
  }

}
