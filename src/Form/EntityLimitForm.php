<?php

namespace Drupal\entity_limit\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EntityLimitForm.
 *
 * @package Drupal\entity_limit\Form
 */
class EntityLimitForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity_limit = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity_limit->label(),
      '#description' => $this->t("Label for the Entity Limit."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $entity_limit->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\entity_limit\Entity\EntityLimit::load',
      ),
      '#disabled' => !$entity_limit->isNew(),
    );

    /* You will need additional form elements for your custom properties. */
    $form['limit'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#description' => $this->t("The number of nodes for this limit. Must be an integer greater than 0 or -1 for no limit"),
      '#required' => TRUE,
      '#default_value' => is_null($entity_limit->get('limit')) ? -1 : $entity_limit->get('limit'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity_limit = $this->entity;
    $status = $entity_limit->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Entity Limit.', [
          '%label' => $entity_limit->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Entity Limit.', [
          '%label' => $entity_limit->label(),
        ]));
    }
    $form_state->setRedirectUrl($entity_limit->urlInfo('collection'));
  }

}
