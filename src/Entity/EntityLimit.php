<?php

namespace Drupal\entity_limit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\entity_limit\EntityLimitInterface;

/**
 * Defines the Entity Limit entity.
 *
 * @ConfigEntityType(
 *   id = "entity_limit",
 *   label = @Translation("Entity Limit"),
 *   handlers = {
 *     "list_builder" = "Drupal\entity_limit\EntityLimitListBuilder",
 *     "form" = {
 *       "add" = "Drupal\entity_limit\Form\EntityLimitForm",
 *       "edit" = "Drupal\entity_limit\Form\EntityLimitForm",
 *       "delete" = "Drupal\entity_limit\Form\EntityLimitDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\entity_limit\EntityLimitHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "entity_limit",
 *   admin_permission = "administer entity_limit settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/entity_limit/{entity_limit}",
 *     "add-form" = "/admin/structure/entity_limit/add",
 *     "edit-form" = "/admin/structure/entity_limit/{entity_limit}/edit",
 *     "delete-form" = "/admin/structure/entity_limit/{entity_limit}/delete",
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

}
