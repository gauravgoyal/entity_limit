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

    /* You will need additional form elements for your custom properties. */
    $form['limit'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#description' => $this->t("The number of nodes for this limit. Must be an integer greater than 0 or -1 for no limit"),
      '#required' => TRUE,
      '#default_value' => is_null($entity_limit->get('limit')) ? ENTITYLIMIT_NO_LIMIT : $entity_limit->get('limit'),
    );

    $limit_by_roles = $this->config('entity_limit.settings')->get('limit_by_roles');
    if ($limit_by_roles == 1) {
      $roles = user_roles(TRUE);
      $allowed_roles = array();
      foreach ($roles as $role) {
        $allowed_roles[$role->id()] = $role->label();
      }
      $form['limit_by_roles'] = array(
        '#type' => 'select',
        '#title' => $this->t('Select Roles to Limit'),
        '#description' => $this->t('Limit will be applied to these roles'),
        '#options' => $allowed_roles,
        '#multiple' => TRUE,
      );
    }

    $limit_by_roles = $this->config('entity_limit.settings')->get('limit_by_roles');
    if ($limit_by_roles == 1) {
      $roles = user_roles(TRUE);
      $allowed_roles = array();
      foreach ($roles as $role) {
        $allowed_roles[$role->id()] = $role->label();
      }
      $form['limit_by_users'] = array(
        '#type' => 'entity_autocomplete',
        '#target_type' => 'user',
        '#title' => $this->t('Select users to apply limit'),
        '#description' => $this->t('Limit will be applied to these users. Seperate multiple users by comma'),
        '#multiple' => TRUE,
      );
    }

    $allowed_entities = $this->config('entity_limit.settings')->get('allowed_entities');
    $saved_entities = $entity_limit->get('entities');
    foreach ($allowed_entities as $entity_type => $name) {
      $form['entities'][$entity_type] = array(
        '#type' => 'details',
        '#title' => $this->t('Limit @name entities', array('@name' => $name)),
      );
      $form['entities'][$entity_type]['enable'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Enable Limit'),
        '#default_value' => !empty($saved_entities[$entity_type]['enable']) ? $saved_entities[$entity_type]['enable'] : 0,
      );

      $bundles = $this->entityManager->getBundleInfo($entity_type);
      if (!empty($bundles)) {
        $options = array();
        foreach ($bundles as $machine_name => $bundle) {
          $options[$machine_name] = $bundle['label'];
        }
        $form['entities'][$entity_type]['bundles'] = array(
          '#type' => 'checkboxes',
          '#title' => $this->t('Select Bundles'),
          '#description' => $this->t('Select bundles of this entity to apply limit'),
          '#options' => $options,
          '#default_value' => !empty($saved_entities[$entity_type]['bundles']) ? $saved_entities[$entity_type]['bundles'] : array(),
        );
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity_limit = $this->entity;
    $entities = $entity_limit->get('entities');
    foreach ($entities as $bundle => $value) {
      if ($value['enable'] == 0) {
        unset($entities[$bundle]);
        continue;
      }
    }
    $entity_limit->set('entities', $entities);
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
