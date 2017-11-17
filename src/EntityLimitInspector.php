<?php

namespace Drupal\entity_limit;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_limit\Entity\EntityLimit;

/**
 * Provide handler for all entity limit usage functions.
 */
class EntityLimitInspector {

  /**
   * @var int
   *   Unilimited limit option value.
   */
  const entityLimitUnlimited = -1;

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
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   *
   * @return bool
   *   True if limit has reached otherwise false.
   */
  public function checkEntityLimits($entity_type_id, $entity_bundle, AccountInterface $account) {
    $access = TRUE;
    $applicable_limit = $this->getApplicableLimit($entity_type_id, $entity_bundle, $account);
    if (!empty($applicable_limit)) {
      switch ($applicable_limit['limit']) {
        case self::entityLimitUnlimited:
          // Unlimited Access.
          break;

        default:
          // Limit is applicable now we need to compare.
          if ($account) {
            $access = $this->compareAccountLimit($account, $applicable_limit['entity_limit'], $applicable_limit['limit']);
          }
          break;
      }
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
    $configStorage = $this->entityManager->getStorage('entity_limit');
    $configurations = $configStorage->loadByProperties([
      'entity_type' => $entity_type_id,
    ]
    );
    return $configurations;
  }

  /**
   * Get applicable limit count and entity_limit object.
   *
   * @param string $entity_type_id
   *   Entity type.
   * @param string $entity_bundle
   *   Entity bundle.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   *
   * @return array|mixed
   *   Array with limit_count and entity_limit object.
   */
  public function getApplicableLimit($entity_type_id, $entity_bundle, AccountInterface $account) {
    $entity_limits = $this->getEntityLimits($entity_type_id);
    $applicable_limits = [];

    foreach ($entity_limits as $key => $entity_limit) {
      // We need to verify entity_limit bundle and plugin applicability.
      $entityBundleFlag = FALSE;

      if (in_array($entity_bundle, $entity_limit->getEntityLimitBundles())) {
        $entityBundleFlag = TRUE;
      }

      if ($entityBundleFlag) {
        $plugin_id = $entity_limit->getPlugin();
        $plugin = $this->pluginManager->createInstance($plugin_id, ['of' => 'configuration values']);
        $priority = $plugin->getPriority();
        $applicable_limits[$priority] = [
          'limit' => $plugin->getLimitCount($account, $entity_limit),
          'entity_limit' => $entity_limit,
        ];
      }
    }

    // Based on priority get final limit.
    ksort($applicable_limits);
    $applicable_limits = reset($applicable_limits);

    return !empty($applicable_limits) ? $applicable_limits : [];
  }

  /**
   * Compare applicable limit with availability.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   * @param \Drupal\entity_limit\Entity\EntityLimit $entityLimit
   *   Entity Limit object.
   * @param $limit
   *   Applicable limit.
   *
   * @return bool
   *   Access.
   */
  public function compareAccountLimit(AccountInterface $account, EntityLimit $entityLimit, $limit) {
    $access = TRUE;
    $query = \Drupal::entityQuery($entityLimit->getEntityLimitType());
    $query->condition('type', $entityLimit->getEntityLimitBundles(), 'IN');
    $query
      ->condition('uid', $account->id());
    $count = count($query->execute());
    if ($count >= (int) $limit) {
      $access = FALSE;
    }

    return $access;
  }

}
