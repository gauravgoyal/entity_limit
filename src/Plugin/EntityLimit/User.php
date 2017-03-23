<?php

namespace Drupal\entity_limit\Plugin\EntityLimitViolation;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_limit\Plugin\EntityLimitPluginBase;
use Drupal\user\Entity\User;

/**
 * Provides a plugin to limit entities per user.
 *
 * @EntityLimit(
 *   id = "user",
 *   title = @Translation("User"),
 * )
 */
class User extends EntityLimitPluginBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['settings'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Select users to apply limit'),
      '#description' => $this->t('Limit will be applied to these users. Seperate multiple users by comma'),
      '#default_value' => !is_null($this->settings) ? User::loadMultiple($this->settings) : array(),
      '#tags' => TRUE,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    if (!empty($configuration['settings'])) {
      foreach ($configuration['settings'] as $key => $value) {
        if (!empty($value['target_id'])) {
          $configuration['settings'][$key] = $value['target_id'];
        }
      }
    }
    parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function processViolation() {
    $user = \Drupal::currentUser();
    if (in_array($user->id(), $this->settings)) {
      return ENTITYLIMIT_APPLY;
    }
    return ENTITYLIMIT_NEUTRAL;
  }

  /**
   *
   */
  public function addConditions(&$query) {
    $user = \Drupal::currentUser();
    $query->condition('uid', $user->id());
  }

}
