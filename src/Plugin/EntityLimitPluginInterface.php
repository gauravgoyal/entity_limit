<?php

namespace Drupal\entity_limit\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_limit\Entity\EntityLimit;

/**
 * Interface for plugins of entity limit.
 */
interface EntityLimitPluginInterface extends PluginFormInterface, PluginInspectionInterface {

  /**
   * Validate account has access based on entity_limit.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Logged in User Account.
   * @param \Drupal\entity_limit\Entity\EntityLimit $entityLimit
   *   Entity Limit Object.
   *
   * @return mixed
   */
  public function validateAccountLimit(AccountInterface $account, EntityLimit $entityLimit);

}
