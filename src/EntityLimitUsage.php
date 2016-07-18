<?php

namespace Drupal\entity_limit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Session\AccountInterface;

/**
 * Provide handler for all entity limit usage functions.
 */
class EntityLimitUsage {

  protected $entityManager;

  protected $account;

  protected $configFactory;

  protected $entityQuery;

  private $configList;

  /**
   * Construct entity_limit usage.
   *
   * @param EntityManagerInterface $entityManager
   *   Entity Manager.
   * @param AccountInterface $account
   *   User's Account.
   * @param ConfigFactoryInterface $configFactory
   *   Config Manager Interface.
   * @param QueryFactory $entityQuery
   *   Query Factory.
   */
  public function __construct(EntityManagerInterface $entityManager, AccountInterface $account, ConfigFactoryInterface $configFactory, QueryFactory $entityQuery) {
    $this->entityManager = $entityManager;
    $this->account = $account;
    $this->configFactory = $configFactory;
    $this->entityQuery = $entityQuery;
    $this->configList = $this->configFactory->listAll('entity_limit.entity_limit');
  }

  /**
   * Check entityLimit violations.
   */
  public function entityLimitViolationCheck($entity_type_id, $bundle = NULL) {
    $configurations = $this->loadAllConfigurations();
    $violations = TRUE;
    foreach ($configurations as $config) {
      $entities = $config->get('entities');
      if ($entities[$entity_type_id] = $entity_type_id) {
        $limit = $config->get('limit');
        if ($limit == ENTITYLIMIT_NO_LIMIT) {
          $violations = FALSE;
        }
        $entityCount = $this->getContent($entity_type_id, $bundle);
        if ($entityCount <= $limit) {
          $violations = FALSE;
        }
      }
    }
    return $violations;
  }

  /**
   * Load all the configuration defined by entity_limit module.
   */
  private function loadAllConfigurations() {
    $loadedConfigurations = $this->configFactory->loadMultiple($this->configList);
    return $loadedConfigurations;
  }

  /**
   * Get all content for entity and bundle.
   */
  public function getContent($entity_type_id, $bundle = NULL) {
    $conditions = !is_null($bundle) ? array('type' => $bundle) : array();
    $result = $this->buildQuery($entity_type_id, $conditions);
    return $result;
  }

  /**
   * Build query for given conditions.
   *
   * @param string $entity_type_id
   *   Entity Type name.
   * @param array $conditions
   *   Condition in the format of array('key' => 'value').
   *
   * @return array | NULL
   *   Result for the above query.
   */
  private function buildQuery($entity_type_id, $conditions = array()) {
    // Use the factory to create a query object for node entities.
    $query = $this->entityQuery->get($entity_type_id);
    if (!empty($conditions)) {
      foreach ($conditions as $key => $value) {
        if (is_array($value)) {
          $query->condition($key, $value, 'IN');
        }
        else {
          $query->condition($key, $value);
        }
      }
    }
    $result = $query->count()->execute();
    return $result;
  }

}
