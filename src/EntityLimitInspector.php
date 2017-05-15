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

  /**
   * Construct entity_limit usage.
   *
   * @param EntityManagerInterface $entityManager
   *   Entity Manager.
   */
  public function __construct(EntityManagerInterface $entityManager, PluginManagerInterface $entityLimit) {
    $this->entityManager = $entityManager;
    $this->entityLimit = $entityLimit;
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
    /**
     * @todo
     */
    // Get all limits applicable to this entity id and bundle.
    $bundleLimits = $this->getBundleLimits($entity_type_id, $entity_bundle);
    // If $account is not specified take logged in user account.
    // Check all limits applicable to this account according to plugins.
    $limits = $this->getAccountLimits($account, $bundleLimits);
    // Check if this account has crossed the limit according to priority of plugins
    // i.e. check user first then role then others.
    $access = $limits->validateAccess();
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
