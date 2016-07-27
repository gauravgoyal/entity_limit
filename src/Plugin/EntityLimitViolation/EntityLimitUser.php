<?php

namespace Drupal\entity_limit\Plugin\EntityLimitViolation;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_limit\Plugin\EntityLimitViolationPluginBase;
use Drupal\user\Entity\User;

/**
 * Provides a plugin to limit entities per user.
 *
 * @EntityLimitViolation(
 *   id = "entity_limit_by_user",
 *   title = @Translation("Limit Entities per user"),
 *   settings = {},
 *   priority = 0,
 * )
 */
class EntityLimitUser extends EntityLimitViolationPluginBase {

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
    foreach ($configuration['settings'] as $key => $value) {
      if (!empty($value['target_id'])) {
        $configuration['settings'][$key] = $value['target_id'];
      }
    }
    parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function processViolation() {
    dpm($this->settings);
    return $this->settings;
  }

}
