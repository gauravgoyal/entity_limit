<?php

namespace Drupal\entity_limit\Plugin\EntityLimit;

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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;
    $form['limits'] = array(
      '#type' => 'container'
    );
    $form['limits'][0]['id'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Select users to apply limit'),
      '#description' => $this->t('Limit will be applied to these users. Seperate multiple users by comma'),
//      '#default_value' => !is_null($this->settings) ? User::loadMultiple($this->settings) : array(),
//      '#tags' => TRUE,
    );
    $form['limits'][0]['limit'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#required' => TRUE,
    );
    $form['limits'][1]['id'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Select users to apply limit'),
      '#description' => $this->t('Limit will be applied to these users. Seperate multiple users by comma'),
//      '#default_value' => !is_null($this->settings) ? User::loadMultiple($this->settings) : array(),
//      '#tags' => TRUE,
    );
    $form['limits'][1]['limit'] = array(
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
