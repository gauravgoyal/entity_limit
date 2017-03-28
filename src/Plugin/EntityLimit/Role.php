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
    $form['limits'] = array(
      '#type' => 'container'
    );
    $form['limits']['id'] = array(
      '#type' => 'select',
      '#title' => $this->t('Select Role to Limit'),
      '#description' => $this->t('Limit will be applied to this role'),
      '#options' => $allowed_roles,
      '#required' => TRUE,
    );
    $form['limits']['limit'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
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
