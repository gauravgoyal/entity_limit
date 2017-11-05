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

    if (!empty($applicable_limits)) {
      $plugin_access = [];
      // For each applicable limits check if account passes the criterion.
      foreach ($applicable_limits as $key => $entity_limit) {
        $plugin_id = $entity_limit->getPlugin();
        $plugin = $this->pluginManager->createInstance($plugin_id, ['of' => 'configuration values']);
        $limit = $plugin->validateAccountLimit($account, $entity_limit);
        $plugin_priority = $plugin->getPriority();
        if (is_array($plugin_access[$entity_limit->get('weight')])) {
          $plugin_access[$entity_limit->get('weight')][$plugin_priority][$key] = $limit;
        }
        else {
          $plugin_access[$entity_limit->get('weight')] = [];
          $plugin_access[$entity_limit->get('weight')][$plugin_priority] = [];
          $plugin_access[$entity_limit->get('weight')][$plugin_priority][$key] = $limit;
        }
      }

      // Sort in the order of entity limit priority.
      ksort($plugin_access);

      // There can be two cases
      // 1. Multiple applicable limits of different priority. In this case
      // we will give access to the top priority item.
      // 2. Multiple applicable limits of same priority. In this case we will
      // goto plugin priority.
      // 2.1. If there are multiple limits of same plugin priority & entity
      // limit priority then we consider the height limit value from the set.
      $priority_limit = reset($plugin_access);
      ksort($priority_limit);
      $access = in_array(TRUE, reset($priority_limit)) ? TRUE : FALSE;
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
