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
    $form['#tree'] = TRUE;

    $num_rows = $form_state->get('num_rows');
    if (empty($num_rows)) {
      $num_rows = $form_state->set('num_rows', 1);
    }

    $roles = user_roles(TRUE);
    $allowed_roles = array();
    foreach ($roles as $role) {
      $allowed_roles[$role->id()] = $role->label();
    }

    $form['limits'] = array(
      '#type' => 'table',
      '#caption' => $this->t('Add limits'),
      '#header' => [$this->t('Role'), $this->t('Limit'), $this->t('Operations')],
      '#prefix' => '<div id="limits-table">',
      '#suffix' => '</div>',
    );

    for ($i = 0; $i < $num_rows; $i++) {
      $form['limits'][$i]['id'] = array(
        '#type' => 'select',
        '#description' => $this->t('Limit will be applied to this role'),
        '#options' => $allowed_roles,
      );

      $form['limits'][$i]['limit'] = array(
        '#type' => 'textfield',
        '#description' => $this->t('Add limit applicable for this user'),
        '#size' => 60,
      );
      $form['limits'][$i]['remove_row'] = array(
        '#type' => 'submit',
        '#value' => t('Remove Row'),
        '#submit' => array([$this, 'removeRow']),
        '#ajax' => [
          'callback' => [$this, 'ajaxCallback'],
          'wrapper' => 'limits-table',
        ],
      );
    }

    $form['add_row'] = array(
      '#type' => 'submit',
      '#value' => t('Add Row'),
      '#submit' => array([$this, 'addRow']),
      '#ajax' => [
        'callback' => [$this, 'ajaxCallback'],
        'wrapper' => 'limits-table',
      ],
    );
    $form_state->setCached(FALSE);
    return $form;
  }

  /**
   * Submit handler for the "remove row" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeRow(array &$form, FormStateInterface $form_state) {
    $num_rows = $form_state->get('num_rows');
    if ($num_rows > 1) {
      $num_rows = $num_rows - 1;
      $form_state->set('num_rows', $num_rows);
    }
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "add row" button.
   *
   * Increment the max counter and causes a form rebuild.
   */
  public function addRow(array &$form, FormStateInterface $form_state) {
    $num_rows = $form_state->get('num_rows');
    $num_rows = $num_rows + 1;
    $form_state->set('num_rows', $num_rows);
    $form_state->setRebuild();
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['limits'];
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
