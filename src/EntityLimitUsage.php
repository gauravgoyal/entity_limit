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

  private $entityTypeId;

  private $bundle;

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
   * Check whether the bundle is included in the configuration.
   *
   * @param array $config
   *   Configuration for entity limit..
   *
   * @return bool
   *   Status of the bundle.
   */
  private function isBundleLimited($config) {
    $limited_bundles = $config->get('entities.' . $this->entityTypeId . '.bundles');
    if (!empty($this->bundle) && in_array($this->bundle, $limited_bundles)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Check whether the entity given in the configuration is enabled or not.
   *
   * @param array $config
   *   Configuraiton..
   *
   * @return bool
   *   Status.
   */
  private function isEntityTypeLimitEnabled($config) {
    $enabled = $config->get('entities.' . $this->entityTypeId . '.enable');
    if ($enabled == ENTITYLIMIT_ENABLED) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Current User Entity Limit Configurations.
   *
   * @param array $configurations
   *   Configuration to check.
   *
   * @return array
   *   All limits for current user.
   */
  public function getCurrentUserEntityLimits($configurations) {
    $currentUserConfigurations = array();
    foreach ($configurations as $config) {
      $current_user = $config->get('limit_by_users.' . $this->account->id());
      if ($current_user == $this->account->id()) {
        $currentUserConfigurations[] = $config;
      }
    }
    return $currentUserConfigurations;
  }

  /**
   * Get limits of current Role.
   *
   * @param array $configurations
   *   Configurations to check.
   *
   * @return array
   *   All the configurations applicable to this role.
   */
  public function getCurrentRoleEntityLimits($configurations) {
    $currentRoleConfigurations = array();
    foreach ($configurations as $config) {
      $roles = $config->get('limit_by_roles');
      $current_user_roles = $this->account->getRoles();
      $common_roles = array_intersect($current_user_roles, $roles);
      if (!empty($common_roles)) {
        $currentRoleConfigurations[] = $config;
      }
    }
    return $currentRoleConfigurations;
  }

  /**
   * Get enabled entity configurations.
   *
   * @param array $configurations
   *   All configuration to check..
   *
   * @return array
   *   Enabled configuration for current entity type and bundle.
   */
  private function getEntityEnabledLimitConfigs($configurations) {
    $enabledConfigs = array();
    foreach ($configurations as $config) {
      if ($this->isEntityTypeLimitEnabled($config) && $this->isBundleLimited($config)) {
        $enabledConfigs[] = $config;
      }
    }
    return $enabledConfigs;
  }

  /**
   * All enabled limit configuration for entity type.
   *
   * @param array $configurations
   *   All entitylimit configurations.
   *
   * @return array
   *   All Enabled configurations.
   */
  protected function getEntityTypeLimits($configurations) {
    $enabledConfigs = array();
    // Check if we have some configuration for current user..
    $currentUserConfigurations = $this->getCurrentUserEntityLimits($configurations);
    if (!empty($currentUserConfigurations)) {
      $configurations = $currentUserConfigurations;
    }
    else {
      // Check if we have some configuration for current user's role..
      $currentRoleConfigurations = $this->getCurrentRoleEntityLimits($configurations);
      if (!empty($currentRoleConfigurations)) {
        $configurations = $currentRoleConfigurations;
      }
    }
    $enabledConfigs = $this->getEntityEnabledLimitConfigs($configurations);
    return $enabledConfigs;
  }

  /**
   * Get configuration limit.
   *
   * @param array $config
   *   Configuration..
   *
   * @return int
   *    Limit
   */
  public function getLimit($config) {
    $limit = $config->get('limit');
    return $limit;
  }

  /**
   * Get maximum limit from the configurations.
   *
   * @param array $configurations
   *   All loaded configuration for entity limit..
   *
   * @return int
   *   Maximum limit.
   */
  protected function getMaximumLimit($configurations) {
    $enabledConfigs = $this->getEntityTypeLimits($configurations);
    $maxLimit = 0;
    foreach ($enabledConfigs as $config) {
      $limit = $this->getLimit($config);
      if ($limit == ENTITYLIMIT_NO_LIMIT) {
        $maxLimit = ENTITYLIMIT_NO_LIMIT;
        break;
      }
      $maxLimit = ($limit > $maxLimit) ? $limit : $maxLimit;
    }
    return $maxLimit;
  }

  /**
   * Check entityLimit violations.
   */
  public function entityLimitViolationCheck($entityTypeId, $bundle = NULL) {
    $this->entityTypeId = $entityTypeId;
    if ($bundle != NULL) {
      $this->bundle = $bundle;
    }
    $configurations = $this->loadAllConfigurations();
    $violations = FALSE;
    if (!empty($configurations)) {
      $configurations = $this->getEntityTypeLimits($configurations);
      $maxLimit = $this->getMaximumLimit($configurations);
      $count = $this->getContent();
      if ($maxLimit != ENTITYLIMIT_NO_LIMIT && $count >= $maxLimit) {
        $violations = TRUE;
      }
    }
    return $violations;
  }

  /**
   * Load all the configuration defined by entity_limit module.
   */
  public function loadAllConfigurations() {
    $loadedConfigurations = $this->configFactory->loadMultiple($this->configList);
    return $loadedConfigurations;
  }

  /**
   * Get all content for entity and bundle.
   */
  public function getContent() {
    $conditions = !is_null($this->bundle) ? array('type' => $this->bundle) : array();
    $result = (int) $this->buildQuery($conditions);
    return $result;
  }

  /**
   * Build query for given conditions.
   *
   * @param array $conditions
   *   Condition in the format of array('key' => 'value').
   *
   * @return array | NULL
   *   Result for the above query.
   */
  private function buildQuery($conditions = array()) {
    // Use the factory to create a query object for node entities.
    $query = $this->entityQuery->get($this->entityTypeId);
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
