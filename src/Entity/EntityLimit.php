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
 *       "add" = "Drupal\entity_limit\Form\Multistep\EntityLimitSettingsForm",
 *       "addLimits" = "Drupal\entity_limit\Form\Multistep\EntityLimitAddLimitForm",
 *       "edit" = "Drupal\entity_limit\Form\Multistep\EntityLimitSettingsForm",
 *       "delete" = "Drupal\entity_limit\Form\EntityLimitDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\entity_limit\EntityLimitHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "entity",
 *   admin_permission = "administer entity_limit settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/entity_limit/{entity_limit}",
 *     "add-form" = "/admin/structure/entity_limit/add",
 *     "addLimits-form" = "/admin/structure/entity_limit/add/limits",
 *     "edit-form" = "/admin/structure/entity_limit/{entity_limit}/edit",
 *     "delete-form" = "/admin/structure/entity_limit/{entity_limit}/delete",
 *     "collection" = "/admin/structure/entity_limit"
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
   * Get entities from entity limit configurations if any.
   *
   * @return array
   *   Array of entities associated with this config.
   */
  public function getEntities() {
    $entities = $this->get('entities');
    return $entities;
  }

  /**
   * Check limit is applicable to entity or not.
   *
   * @param string $entityTypeId
   *   Entity type which needs to be checked.
   *
   * @return bool
   *   Return whether limit is applicable to this entity or not.
   */
  public function isLimitApplicableToEntity($entityTypeId) {
    $applicable = FALSE;
    foreach ($this->getEntities() as $entityType => $value) {
      if ($entityType == $entityTypeId && $value['enable'] == 1) {
        $applicable = TRUE;
      }
    }
    return $applicable;
  }

  /**
   * Check limit is applicable for bundle.
   *
   * @param string $bundle
   *   Bundle type which needs to be checked.
   *
   * @return bool
   *   Return applicablility status.
   */
  public function isLimitApplicableToBundle($bundle) {
    $applicable = FALSE;
    foreach ($this->getEntities() as $value) {
      if (in_array($bundle, $value['bundles'])) {
        $applicable = TRUE;
      }
    }
    return $applicable;
  }

  /**
   * Get all limit plugins.
   */
  public function violations($instance_id = NULL) {
    if (!isset($this->violationCollection)) {
      $this->violationCollection = new EntityLimitPluginCollection(\Drupal::service('plugin.manager.entity_limit_violations'), $this->violations);
      $this->violationCollection->sort();
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

  /**
   * Build query to get the count for current scenerio.
   *
   * @param string $entityTypeId
   *   EntityType to check.
   * @param string $bundle
   *   Bundle of above entity type.
   *
   * @return object
   *   Object of queryinterface,
   */
  public function getQuery($entityTypeId, $bundle) {
    $query = NULL;
    if ($this->isLimitApplicableToEntity($entityTypeId)) {
      $query = \Drupal::entityQuery($entityTypeId);
      if ($this->isLimitApplicableToBundle($bundle)) {
        $query->condition('type', $bundle);
      }
    }
    return $query;
  }

  /**
   * Get all enabled bundles for current entity limit.
   *
   * @param string $entityTypeId
   *   Entitytype to return bundles for.
   *
   * @return array
   *   All enabled bundles for this entity.
   */
  public function getBundles($entityTypeId) {
    $bundles = array();
    $entities = $this->getEntities();
    if (!empty($entities[$entityTypeId]['bundles'])) {
      $bundles = $entities[$entityTypeId]['bundles'];
    }
    return $bundles;
  }

}
