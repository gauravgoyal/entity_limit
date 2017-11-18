<?php

namespace Drupal\entity_limit\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\entity_limit\Entity\EntityLimit;

/**
 * Interface for plugins of entity limit.
 */
interface EntityLimitPluginInterface extends PluginFormInterface, PluginInspectionInterface {

  /**
   * Get applicable limit count for account based on entity_limit.
   *
   * @param \Drupal\entity_limit\Entity\EntityLimit $entityLimit
   *   Entity Limit Object.
   */
  public function getLimitCount(EntityLimit $entityLimit);

  /**
   * Compare limits and provide access.
   *
   * @param int $limit
   *   The limit.
   * @param \Drupal\entity_limit\Entity\EntityLimit $entityLimit
   *   The entity limit.
   *
   * @return bool
   *   Access TRUE|FALSE
   */
  public function checkAccess($limit, EntityLimit $entityLimit);

}
