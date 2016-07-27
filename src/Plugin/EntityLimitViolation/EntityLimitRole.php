<?php

namespace Drupal\entity_limit\Plugin\EntityLimitViolation;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_limit\Plugin\EntityLimitViolationPluginBase;

/**
 * Provides a plugin to limit entities per role.
 *
 * @EntityLimitViolation(
 *   id = "entity_limit_by_role",
 *   title = @Translation("Limit Entities per role"),
 *   settings = {},
 *   priority = 1,
 * )
 */
class EntityLimitRole extends EntityLimitViolationPluginBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
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
      '#multiple' => TRUE,
      '#default_value' => $this->settings,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function processViolation() {
    return $this->settings;
  }

}
