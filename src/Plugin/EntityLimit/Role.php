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
      '#default_value' => $this->settings,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    if (!empty($configuration['settings']) && !is_array($configuration['settings'])) {
      $configuration['settings'] = array($configuration['settings']);
    }
    parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function processViolation() {
    $user = \Drupal::currentUser();
    $roles = $user->getRoles();
    if (array_intersect($this->settings, $roles)) {
      return ENTITYLIMIT_APPLY;
    }
    return ENTITYLIMIT_NEUTRAL;
  }

  /**
   * {@inheritdoc}
   */
  public function addConditions(&$query) {
    $user = \Drupal::currentUser();
    $roles = $user->getRoles();
    $roles = array_intersect($this->settings, $roles);
    if (in_array('authenticated', $roles)) {
      $query->condition('uid', 0, '!=');
    }
    else {
      $role_users = \Drupal::service('entity_type.manager')->getStorage('user')->getQuery();
      $role_users->condition('roles', 'authenticated');
      $uids = $role_users->execute();
      $query->condition('uid', $uids, 'IN');
    }
  }

}
