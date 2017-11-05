<?php

namespace Drupal\entity_limit\Plugin;

use Drupal\Core\Form\FormStateInterface;
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
   * An associative array containing the configured settings of this filter.
   *
   * @var array
   */
  public $limits = array();

  /**
   * The weight of this filter compared to others in a filter collection.
   *
   * @var int
   */
  public $weight = 0;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
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
  public function getConfiguration() {
    return [
      'weight' => $this->weight,
      'limits' => $this->limits,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    if (isset($configuration['limits'])) {
      $this->limits = $configuration['limits'];
    }

    if (isset($configuration['weight'])) {
      $this->weight = (int) $configuration['weight'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => $this->pluginDefinition['weight'] ?: 0,
      'limits' => $this->limits,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Implementations should work with and return $form. Returning an empty
    // array here allows the text format administration form to identify whether
    // the filter plugin has any settings form elements.
    return [];
  }

}
