<?php

namespace Drupal\entity_limit\Plugin\EntityLimit;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_limit\Plugin\EntityLimitPluginBase;

/**
 * Provides a plugin to limit entities per role.
 *
 * @EntityLimit(
 *   id = "role",
 *   title = @Translation("Role"),
 * )
 */
class Role extends EntityLimitPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $roles = user_roles(TRUE);
    $allowed_roles = array();
    foreach ($roles as $role) {
      $allowed_roles[$role->id()] = $role->label();
    }
    $form['settings'] = array(
      '#type' => 'select',
      '#title' => $this->t('Select Roles to Limit'),
      '#description' => $this->t('Limit will be applied to these roles'),
      '#options' => $allowed_roles,
      '#default_value' => $this->settings,
      '#required' => TRUE,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function execute() {

  }

}
