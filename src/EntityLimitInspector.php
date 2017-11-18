<?php

namespace Drupal\entity_limit;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Provide handler for all entity limit usage functions.
 */
class EntityLimitInspector {

  /**
   * Unilimited limit option value.
   *
   * @var int
   */
  const ENTITYLIMITNOLIMIT = -1;

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
   * Gets the entity limits.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   *
   * @return array
   *   The entity limits.
   */
  public function getEntityLimits($entity_type_id) {
    $configStorage = $this->entityManager->getStorage('entity_limit');
    $configurations = $configStorage->loadByProperties([
      'entity_type' => $entity_type_id,
    ]
    );
    return $configurations;
  }

  /**
   * Check whether user has crossed the entity limits.
   *
   * @param string $entity_type_id
   *   Entity type.
   * @param string $entity_bundle
   *   Entity bundle.
   *
   * @return array|mixed
   *   Array with limit_count and entity_limit object.
   */
  public function checkEntityLimitAccess($entity_type_id, $entity_bundle) {
    $entity_limits = $this->getBundleLimits($entity_type_id, $entity_bundle);
    $applicable_limits = [];
    $plugin = [];

    if (!empty($entity_limits)) {
      foreach ($entity_limits as $entity_limit) {
        $plugin_id = $entity_limit->getPlugin();
        if (!isset($plugin[$plugin_id])) {
          $plugin[$plugin_id] = $this->pluginManager->createInstance($plugin_id, ['of' => 'configuration values']);
        }
        $plugin_priority = $plugin[$plugin_id]->getPriority();
        $weight = $entity_limit->get('weight');
        $limit_count = $plugin[$plugin_id]->getLimitCount($entity_limit);
        if (isset($applicable_limits[$weight]) && is_array($applicable_limits[$weight])) {
          $applicable_limits[$weight][$plugin_priority][$limit_count] = $entity_limit;
        }
        else {
          $applicable_limits[$weight] = [];
          $applicable_limits[$weight][$plugin_priority] = [];
          $applicable_limits[$weight][$plugin_priority][$limit_count] = $entity_limit;
        }
      }

      // Sort in the order of entity limit priority.
      ksort($applicable_limits);
      // There can be two cases
      // 1. Multiple applicable limits of different priority. In this case
      // we will give access to the top priority item.
      // 2. Multiple applicable limits of same priority. In this case we will
      // goto plugin priority.
      // 2.1. If there are multiple limits of same plugin priority & entity
      // limit priority then we consider the height limit value from the set.
      $applicable_limits = reset($applicable_limits);
      ksort($applicable_limits);
      $applicable_limits = reset($applicable_limits);
    }

    $access = !empty($applicable_limits) ? $this->compareLimits($applicable_limits, $plugin) : TRUE;
    return $access;
  }

  /**
   * Compare applicable limit with availability.
   *
   * @param array $applicable_limits
   *   Array of Entity Limit objects.
   *
   * @return bool
   *   Access.
   */
  public function compareLimits(array $applicable_limits, $plugin) {
    $access = TRUE;
    if (!empty($applicable_limits)) {
      if (array_key_exists(self::ENTITYLIMITNOLIMIT, $applicable_limits)) {
        $access = TRUE;
      }
      else {
        $max_limit = max(array_keys($applicable_limits));
        $plugin_type = $applicable_limits[$max_limit]->getPlugin();
        $access = $plugin[$plugin_type]->checkAccess($max_limit, $applicable_limits[$max_limit]);
      }
    }
    return $access;
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
  public function getBundleLimits($entity_type_id, $entity_bundle) {
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
