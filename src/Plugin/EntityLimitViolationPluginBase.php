<?php

namespace Drupal\entity_limit\Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Abstract class for implementation of EntityLimitViolationPluginInterface.
 *
 * @see \Drupal\entity_limit\Annotation\EntityLimitViolation.
 * @see \Drupal\entity_limit\EntityLimitViolationManager
 * @see \Drupal\entity_limit\Plugin\EntityLimitViolationPluginInterface
 * @see plugin_api
 */
abstract class EntityLimitViolationPluginBase extends PluginBase implements EntityLimitViolationPluginInterface {

  /**
   * An associative array containing the configured settings of this filter.
   *
   * @var array
   */
  public $settings = array();

  /**
   * An associative array containing the configured settings of this filter.
   *
   * @var array
   */
  public $priority;

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
  public function setConfiguration(array $configuration) {
    if (isset($configuration['settings'])) {
      $this->settings = $configuration['settings'];
    }
    if (isset($configuration['priority'])) {
      $this->priority = $configuration['priority'];
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array(
      'settings' => $this->settings,
      'priority' => $this->priority,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'settings' => $this->pluginDefinition['settings'],
      'priority' => $this->pluginDefinition['priority'],
    );
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
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Implementations should work with and return $form. Returning an empty
    // array here  identify whether the plugin has any settings form elements.
    return array();
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
  public function processViolation() {
    return $this->settings;
  }

}
