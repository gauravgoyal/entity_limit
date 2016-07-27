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

  protected $violationManager;

  /**
   * Construct entity_limit usage.
   *
   * @param EntityManagerInterface $entityManager
   *   Entity Manager.
   */
  public function __construct(EntityManagerInterface $entityManager, PluginManagerInterface $violationManager) {
    $this->entityManager = $entityManager;
    $this->violationStorage = $entityManager->getStorage('entity_limit');
    $this->violationManager = $violationManager;
  }

  /**
   * Check entity limit violations.
   */
  public function entityLimitViolationCheck($entityTypeId, $bundle = NULL) {
    $access = FALSE;
    $applicableLimits = $this->applicableLimits($entityTypeId, $bundle);
    foreach ($this->violationManager->getDefinitions() as $key => $definition) {
      $priorityList[$definition['priority']] = $key;
    }
    ksort($priorityList);

    // Get priority limit using plugins for current entity & bundle.
    foreach ($priorityList as $plugin_id) {
      if (isset($applicableLimits[$plugin_id])) {
        $applicableLimits = $applicableLimits[$plugin_id];
        break;
      }
    }

    // Compare limit from final applicable limits.
    foreach ($applicableLimits as $value) {
      $violation = $value['violation'];
      $entityLimit = $value['entity'];
      $limit = $entityLimit->getLimit();
      $query = $entityLimit->getQuery($entityTypeId, $bundle);
      $violation->addConditions($query);
      $count = $query->count()->execute();
      if ($count >= $limit) {
        $access = TRUE;
        break;
      }
    }
    return $access;
  }

  /**
   * Get all applicable limits for the given entity type and bundle.
   *
   * @return array
   *   Applicable limits for entity type and bundle.
   */
  public function applicableLimits($entityTypeId, $bundle) {
    $applicableLimits = array();
    foreach ($this->enabledViolations() as $entity_limit_name => $entity_limit) {
      if ($entity_limit->isLimitApplicableToEntity($entityTypeId)) {
        if (empty($entity_limit->getBundles($entityTypeId)) || $entity_limit->isLimitApplicableToBundle($bundle)) {
          foreach ($entity_limit->violations() as $key => $violation) {
            if ($violation->processViolation() == ENTITYLIMIT_APPLY) {
              $applicableLimits[$key][$entity_limit_name]['violation'] = $violation;
              $applicableLimits[$key][$entity_limit_name]['entity'] = $entity_limit;
            }
          }
        }
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
