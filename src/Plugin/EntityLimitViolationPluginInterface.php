<?php

namespace Drupal\entity_limit\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * To define entity limits, we define violation plugins.
 */
interface EntityLimitViolationPluginInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Generates a entity limit violation's settings form.
   *
   * @param array $form
   *   A minimally prepopulated form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the (entire) configuration form.
   *
   * @return array
   *   The $form array with additional form elements for the settings of this
   *   filter. The submitted form values should match $this->settings.
   */
  public function settingsForm(array $form, FormStateInterface $form_state);

  /**
   * Violation is processed.
   *
   * @return int
   *   Limit for current violation.
   */
  public function processViolation();

}
