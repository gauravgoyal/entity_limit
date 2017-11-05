<?php

namespace Drupal\entity_limit\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_limit\Entity\EntityLimit;

/**
 * Interface for plugins of entity limit.
 */
interface EntityLimitPluginInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Generates a limit's settings form.
   *
   * @param array $form
   *   A minimally prepopulated form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the (entire) configuration form.
   *
   * @return array
   *   The $form array with additional form elements for the settings of this
   *   filter. The submitted form values should match $this->limits.
   */
  public function settingsForm(array $form, FormStateInterface $form_state);

  /**
   * Validate account has access based on entity_limit.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Logged in User Account.
   * @param \Drupal\entity_limit\Entity\EntityLimit $entityLimit
   *   Entity Limit Object.
   *
   * @return mixed
   *   Account entity limits.
   */
  public static function validateAccountLimit(AccountInterface $account, EntityLimit $entityLimit);

}
