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

  public $configList;

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
  public function isBundleLimited($config, $entityTypeId, $bundle) {
    $limited_bundles = $config->get('entities.' . $entityTypeId . '.bundles');
    if (!empty($bundle) && in_array($bundle, $limited_bundles)) {
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
  public function isEntityTypeLimitEnabled($config, $entityTypeId) {
    $enabled = $config->get('entities.' . $entityTypeId . '.enable');
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
  public function getEntityEnabledLimitConfigs($configurations, $entityTypeId, $bundle) {
    $enabledConfigs = array();
    foreach ($configurations as $config) {
      if ($this->isEntityTypeLimitEnabled($config, $entityTypeId) && $this->isBundleLimited($config, $entityTypeId, $bundle)) {
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
  public function getEntityTypeLimits($configurations, $entityTypeId, $bundle = NULL) {
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
    $enabledConfigs = $this->getEntityEnabledLimitConfigs($configurations, $entityTypeId, $bundle);
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
   * @param array $enabledConfigs
   *   All loaded configuration for entity limit..
   *
   * @return int
   *   Maximum limit.
   */
  public function getMaximumLimit($enabledConfigs) {
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
    $configurations = $this->loadAllConfigurations();
    $violations = FALSE;
    if (!empty($configurations)) {
      $configurations = $this->getEntityTypeLimits($configurations, $entityTypeId, $bundle);
      $maxLimit = $this->getMaximumLimit($configurations);
      if ($maxLimit != ENTITYLIMIT_NO_LIMIT) {
        $count = $this->getContent($entityTypeId, $bundle);
        if ($count >= $maxLimit) {
          $violations = TRUE;
        }
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
  public function getContent($entityTypeId, $bundle) {
    $conditions = !is_null($bundle) ? array('type' => $bundle) : array();
    $result = (int) $this->buildQuery($entityTypeId, $conditions);
    return $result;
  }

  /**
   * Build query for given conditions.
   *
   * @param string $entityTypeId
   *   Entity type id.
   * @param array $conditions
   *   Condition in the format of array('key' => 'value').
   *
   * @return array | NULL
   *   Result for the above query.
   */
  public function buildQuery($entityTypeId, $conditions = array()) {
    // Use the factory to create a query object for node entities.
    $query = $this->entityQuery->get($entityTypeId);
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
