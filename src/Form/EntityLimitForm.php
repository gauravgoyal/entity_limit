<?php

namespace Drupal\entity_limit\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityLimitForm.
 *
 * @package Drupal\entity_limit\Form
 */
class EntityLimitForm extends EntityForm {

  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
    $container->get('entity.manager')
    );
  }

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
      '#default_value' => is_null($entity_limit->get('limit')) ? ENTITYLIMIT_NO_LIMIT : $entity_limit->get('limit'),
    );

    $entity_manager = $this->entityManager->getEntityTypeLabels(TRUE);
    $content_entities = array_values($entity_manager['Content']);
    $content_entities_key = array_keys($entity_manager['Content']);
    $content_entities = array_combine($content_entities_key, $content_entities);

    $form['entities'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Limit Content Entities'),
      '#description' => $this->t('Limit will be applied to all selected entities'),
      '#options' => $content_entities,
      '#multiple' => TRUE,
      '#default_value' => !empty($entity_limit->get('entities')) ? $entity_limit->get('entities') : array(),
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
