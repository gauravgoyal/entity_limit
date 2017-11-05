<?php

namespace Drupal\entity_limit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\entity_limit\EntityLimitInterface;
use Drupal\entity_limit\EntityLimitPluginCollection;

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
   * Entity Limit Plugin.
   *
   * @var string
   */
  protected $plugin;

  /**
   * Entity type for which the limit is applied.
   *
   * @var string
   */
  protected $entity_type;

  /**
   * Entity bundles for which the limit is applied.
   *
   * @var array
   */
  protected $entity_bundles = [];

  /**
   * Applicable limits forthis entity.
   *
   * @var array
   */
  protected $limits = [];

  /**
   * Holds the collection of entity limit plugins that are used by this entity.
   *
   * @var \Drupal\EntityLimit\EntityLimitPluginCollection
   */
  protected $entityLimitPluginCollection;

  /**
   * Returns the entity limit plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The entity limit plugin manager.
   */
  protected function getEntityLimitPluginManager() {
    return \Drupal::service('plugin.manager.entity_limit');
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

  /**
   * {@inheritdoc}
   */
  public function getEntityLimitPluginCollection() {
    if (!$this->entityLimitPluginCollection) {
      $this->entityLimitPluginCollection = new EntityLimitPluginCollection($this->getEntityLimitPluginManager(), $this->limits);
      $this->entityLimitPluginCollection->sort();
    }
    return $this->entityLimitPluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return array('limits' => $this->getEntityLimitPluginCollection());
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getEntityLimitPluginCollection()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function setPlugin($plugin_id) {
    $this->plugin = $plugin_id;
    $this->getEntityLimitPluginCollection()->addInstanceId($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    return $this->getPlugin()->getPluginDefinition();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array $entities) {
    return $this->getPlugin()->executeMultiple($entities);
  }

  /**
   * {@inheritdoc}
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    /** @var \Drupal\system\ActionConfigEntityInterface $a */
    /** @var \Drupal\system\ActionConfigEntityInterface $b */
    // $a_type = $a->getType();
    // $b_type = $b->getType();
    // if ($a_type != $b_type) {
    //   return strnatcasecmp($a_type, $b_type);
    // }.
    return parent::sort($a, $b);
  }

}
