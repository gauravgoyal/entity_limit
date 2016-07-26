<?php

namespace Drupal\entity_limit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\entity_limit\EntityLimitInterface;
use Drupal\entity_limit\EntityLimitPluginCollection;

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
 *       "delete" = "Drupal\entity_limit\Form\EntityLimitDeleteForm",
 *       "settings" = "Drupal\entity_limit\Form\EntityLimitSettingsForm"
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
 *     "collection" = "/admin/structure/entity_limit",
 *     "settings" = "/admin/config/content/entity_limit"
 *   }
 * )
 */
class EntityLimit extends ConfigEntityBase implements EntityLimitInterface, EntityWithPluginCollectionInterface {

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
   * Get all limit violations.
   *
   * @var array
   */
  protected $violationCollection;

  /**
   * Get all configuration for a violation plugin.
   *
   * @var array
   */
  protected $violations = array();

  /**
   * Get Limit.
   *
   * @return int
   *   Limit for this configuration.
   */
  public function getLimit() {
    $limit = $this->get('limit');
    return $limit;
  }

  /**
   * Get Enable configuration.
   *
   * @return int
   *   Enabled value for this configuration.
   */
  public function getEnable($entityTypeId) {
    $enable = $this->get('entities.' . $entityTypeId . '.enable');
    return $enable;
  }

  /**
   * Get bundles from entity limit configurations if any.
   *
   * @return array
   *   Array of bundles associated with this config.
   */
  public function getBundles($entityTypeId) {
    $bundles = $this->get('entities.' . $entityTypeId . '.bundles');
    return $bundles;
  }

  /**
   * Get all limit plugins.
   */
  public function violations($instance_id = NULL) {
    if (!isset($this->violationCollection)) {
      $this->violationCollection = new EntityLimitPluginCollection(\Drupal::service('plugin.manager.entity_limit_violations'), $this->violations);
      $this->violationCollection->getAll();
    }
    return $this->violationCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return array('violations' => $this->violations());
  }

  /**
   * {@inheritdoc}
   */
  public function setViolationConfig($instance_id, $configuration) {
    $this->violations[$instance_id] = $configuration;
    if (isset($this->violationCollection)) {
      $this->violationCollection->setInstanceConfiguration($instance_id, $configuration);
    }
    return $this;
  }

}
