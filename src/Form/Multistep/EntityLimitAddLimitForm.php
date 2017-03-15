<?php
/**
 * @file
 * Contains \Drupal\entity_limit\Form\Multistep\EntityLimitAddLimitForm.
 */

namespace Drupal\entity_limit\Form\Multistep;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class EntityLimitAddLimitForm extends EntityLimitFormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'addLimits';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $form['actions']['previous'] = array(
      '#type' => 'link',
      '#title' => $this->t('Previous'),
      '#attributes' => array(
        'class' => array('button'),
      ),
      '#weight' => 0,
      '#url' => Url::fromRoute('entity.entity_limit.add_form'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}