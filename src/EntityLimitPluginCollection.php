<?php

namespace Drupal\entity_limit;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\DefaultLazyPluginCollection;

/**
 * Provides a container for lazily loading Entity Limit violation plugins.
 */
class EntityLimitPluginCollection extends DefaultLazyPluginCollection {

  protected $definitions;

  /**
   * {@inheritdoc}
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

  /**
   * Get all the plugins for violation.
   */
  public function getAll() {
    if (!$this->definitions) {
      $this->definitions = $this->manager->getDefinitions();
    }

    foreach ($this->definitions as $plugin_id => $definition) {
      if (!isset($this->pluginInstances[$plugin_id])) {
        $this->initializePlugin($plugin_id);
      }

    }
    return $this->pluginInstances;
  }

  /**
   * {@inheritdoc}
   */
  public function sort() {
    $this->getAll();
    return parent::sort();
  }

  /**
   * {@inheritdoc}
   */
  public function sortHelper($aID, $bID) {
    $a = $this->get($aID);
    $b = $this->get($bID);

    if ($a->priority != $b->priority) {
      return $a->priority < $b->priority ? -1 : 1;
    }

    return parent::sortHelper($aID, $bID);
  }

  /**
   * {@inheritdoc}
   */
  protected function initializePlugin($instance_id) {
    $configuration = $this->manager->getDefinition($instance_id);
    // Merge the actual configuration into the default configuration.
    if (isset($this->configurations[$instance_id])) {
      $configuration = NestedArray::mergeDeep($configuration, $this->configurations[$instance_id]);
    }
    $this->configurations[$instance_id] = $configuration;
    parent::initializePlugin($instance_id);
  }

}
