<?php
/**
 * @file
 * Contains \Drupal\entity_limit\Form\Multistep\EntityLimitSettingsForm.
 */

namespace Drupal\entity_limit\Form\Multistep;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

class EntityLimitSettingsForm extends EntityLimitFormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'entity_limit_settings';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);
    $form['#tree'] = TRUE;

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

    $plugins = ['user' => t('User'), 'role' => t('Role')];
    $form['plugins'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Select plug-in'),
      '#options' => $plugins,
      '#required' => TRUE,
    );

    $entity_types = $this->getContentEntities();
    $form['entity_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Select entity type'),
      '#options' => $entity_types,
      '#required' => TRUE,
      '#default_value' => 0,
      '#ajax' => array(
        'callback' => array($this, 'Drupal\entity_limit\Form\Multistep\EntityLimitSettingsForm::entityBundleCallback'),
        'effect' => 'fade',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ),
    );
    $form['entity_bundles'] = array(
      '#type' => 'markup',
      '#prefix' => '<div id="bundles">',
      '#suffix' => '</div>',
    );

    $form['actions']['submit']['#value'] = $this->t('Next');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.entity_limit.add_limits_form');
  }
  public function entityBundleCallback(array &$form, FormStateInterface &$form_state) {
    $entity_type = $form_state->getValue('entity_type');
    $bundles = $this->entityManager->getBundleInfo($entity_type);
    $options = array();
    if (!empty($bundles)) {
      foreach ($bundles as $machine_name => $bundle) {
        $options[$machine_name] = $bundle['label'];
      }
    }
    $form['entity_bundles'] = array(
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#title' => $this->t('Select ' . $entity_type . ' bundles'),
      '#description' => $this->t('Select bundles to apply limit.'),
      '#options' => $options,
      '#prefix' => '<div id = "bundles">',
      '#suffix' => '</div>',
    );
    Checkboxes::processCheckboxes($form['entity_bundles'], $form_state, $form);
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#bundles', $form['entity_bundles']));
    return $response;
  }
}