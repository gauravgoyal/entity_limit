<?php

namespace Drupal\entity_limit;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Provide handler for all entity limit usage functions.
 */
class EntityLimitUsage {

  protected $entityManager;

  protected $violationStorage;

  protected $violationManger;

  /**
   * Construct entity_limit usage.
   *
   * @param EntityManagerInterface $entityManager
   *   Entity Manager.
   */
  public function __construct(EntityManagerInterface $entityManager, PluginManagerInterface $violationManger) {
    $this->entityManager = $entityManager;
    $this->violationStorage = $entityManager->getStorage('entity_limit');
    $this->violationManger = $violationManger;
  }

  /**
   * Check entity limit violations.
   */
  public function entityLimitViolationCheck($entityTypeId, $bundle = NULL) {
    foreach ($this->applicableLimits($entityTypeId, $bundle) as $entity_limit) {
      foreach ($entity_limit->getPluginCollections() as $violation) {
        $violation->processViolation();
      }
    }
  }

  /**
   * Get all applicable limits for the given entity type and bundle.
   *
   * @return array
   *   Applicable limits for entity type and bundle.
   */
  public function applicableLimits($entityTypeId, $bundle = NULL) {
    $applicableLimits = array();
    foreach ($this->enabledViolations() as $entity_limit) {
      if ($entity_limit->isLimitApplicable($entityTypeId, $bundle)) {
        $applicableLimits[$entity_limit->id()] = $entity_limit;
      }
    }
    return $applicableLimits;
  }

  /**
   * Get all enabled entity limit violation plugins.
   *
   * @return array
   *   All enabled violations.
   */
  public function enabledViolations() {
    return $this->violationStorage->loadByProperties(['status' => TRUE]);
  }

}
