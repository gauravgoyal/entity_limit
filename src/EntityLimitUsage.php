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
   *
   */
  public function entityLimitViolationCheck($entityTypeId, $bundle = NULL) {
    dpm($this->applicableLimits($entityTypeId, $bundle));
    dpm($this->violationManger->getDefinitions());
  }

  /**
   *
   */
  public function applicableLimits($entityTypeId, $bundle = NULL) {
    $applicableLimits = array();
    foreach ($this->enabledViolations() as $key => $entity_limit) {
      if ($entity_limit->isLimitApplicable($entityTypeId, $bundle)) {
        $applicableLimits[$key] = $entity_limit;
      }
    }
    return $applicableLimits;
  }

  /**
   *
   */
  public function enabledViolations() {
    return $this->violationStorage->loadByProperties(['status' => TRUE]);
  }

}
