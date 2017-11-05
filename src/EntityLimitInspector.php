<?php

namespace Drupal\entity_limit;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Provide handler for all entity limit usage functions.
 */
class EntityLimitInspector {

  protected $entityManager;

  protected $entityLimit;

  protected $pluginManager;

  /**
   * Construct entity_limit usage.
   *
   * @param EntityManagerInterface $entityManager
   *   Entity Manager.
   */
  public function __construct(EntityManagerInterface $entityManager, EntityLimitUsage $entityLimit, PluginManagerInterface $pluginManager) {
    $this->entityManager = $entityManager;
    $this->entityLimit = $entityLimit;
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
    $applicableLimits = $this->entityLimit->getApplicableLimits($entity_type_id, $entity_bundle);
    $available_plugins = $this->pluginManager->getDefinitions();

    // Foreach applicable limits check if account passes the criterion.
    foreach ($applicableLimits as $entity_limit) {
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
   *
   */
  public function getAccountLimits($account, $bundleLimits) {
    /**
     * @todo
     */
    // Get all entity limits.
    //
  }

}
