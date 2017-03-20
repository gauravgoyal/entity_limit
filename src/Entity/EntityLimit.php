<?php

namespace Drupal\entity_limit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\entity_limit\EntityLimitInterface;

/**
 * Defines the Entity limit configuration entity.
 *
 * @ConfigEntityType(
 *   id = "entity_limit",
 *   label = @Translation("Entity Limit"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\entity_limit\Form\EntityLimitAddForm",
 *       "edit" = "Drupal\entity_limit\Form\EntityLimitAddForm",
 *       "delete" = "Drupal\entity_limit\Form\EntityLimitDeleteForm",
 *       "manage" = "Drupal\entity_limit\Form\EntityLimitAddLimitForm",
 *     },
 *     "list_builder" = "Drupal\entity_limit\EntityLimitListBuilder",
 *   },
 *   config_prefix = "entity_limit",
 *   admin_permission = "administer entity limit",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/entity_limit/edit/{entity_limit}",
 *     "delete-form" = "/admin/structure/entity_limit/delete/{entity_limit}",
 *     "manage-form" = "/admin/structure/entity_limit/manage/{entity_limit}",
 *     "collection" = "/admin/structure/entity_limit"
 *   }
 * )
 */
class EntityLimit extends ConfigEntityBase implements EntityLimitInterface {

  /**
   * The Entity Limit ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Entity Limit label.
   *
   * @var string
   */
  protected $label;

  /**
   * Entity Limit Plugin.
   *
   * @var string
   */
  protected $plugin;

  /**
   * @var string
   */
  protected $entity_type;

  /**
   * @var array
   */
  protected $entity_bundles;

  /**
   * @var array
   */
  protected $limits;

  /**
   * @return string
   */
  public function getPlugin() {
    return $this->plugin;
  }

  /**
   * @return string
   */
  public function getEntityLimitType() {
    return $this->entity_type;
  }

  public function getEntityLimitBundles() {
    // @todo: We're not getting the value for ajaxified form elements, need to look into this.
    return $this->entity_bundles;
  }

}
