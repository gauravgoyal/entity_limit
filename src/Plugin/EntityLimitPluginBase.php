<?php

namespace Drupal\entity_limit\Plugin;

use Drupal\Core\Plugin\PluginBase;

/**
 * Abstract class for implementation of EntityLimitViolationPluginInterface.
 *
 * @see \Drupal\entity_limit\Annotation\EntityLimit
 * @see \Drupal\entity_limit\EntityLimitPluginManager
 * @see \Drupal\entity_limit\Plugin\EntityLimitPluginInterface
 * @see plugin_api
 */
abstract class EntityLimitPluginBase extends PluginBase implements EntityLimitPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority() {
    return $this->pluginDefinition['priority'];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

}
