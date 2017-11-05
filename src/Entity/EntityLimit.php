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
 *     "weight" = "weight",
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
   * Unique machine name of the entity limit.
   *
   * @var string
   */
  protected $id;

  /**
   * Unique label of the entity limit.
   *
   * @var string
   */
  protected $label;

  /**
   * Unique machine name of the entity limit plugin used for this entity.
   *
   * @var string
   */
  protected $plugin;

  /**
   * Unique machine name of the entity used in this entity limit configuration.
   *
   * @var string
   */
  protected $entity_type;

  /**
   * Bundles of the entity used in this entity limit configuration.
   *
   * @var string
   */
  protected $entity_bundles;

  /**
   * Limit configurations in this entity limit configuration.
   *
   * @var string
   */
  protected $limits;

  /**
   * Weight of this entity in the entity limit selector.
   *
   * The first/lowest entity limit has lowest priority.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * Gets the plugin.
   *
   * @return string
   *   The plugin.
   */
  public function getPlugin() {
    return $this->plugin;
  }

  /**
   * Gets the entity limit type.
   *
   * @return string
   *   The entity limit type.
   */
  public function getEntityLimitType() {
    return $this->entity_type;
  }

  /**
   * Gets the entity limit bundles.
   *
   * @return array
   *   The entity limit bundles.
   */
  public function getEntityLimitBundles() {
    return $this->entity_bundles;
  }

}
