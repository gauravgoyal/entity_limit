<?php

namespace Drupal\entity_limit\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Configuration form for entity limit.
 */
class EntityLimitAddForm extends EntityForm {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Constructs the NodeTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   The Plugin Manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, PluginManagerInterface $plugin_manager) {
    $this->entityManager = $entity_manager;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.entity_limit')
    );
  }

  /**
   * {@inheritdoc}.
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
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => array(
        'exists' => ['Drupal\entity_limit\Entity\EntityLimit', 'load'],
        'source' => array('label'),
      ),
      '#disabled' => !$entity_limit->isNew(),
    );

    $plugins = $this->pluginManager->getDefinitions();
    $plugins_data = array();
    foreach ($plugins as $plugin_id => $plugin) {
      $plugins_data[$plugin_id] = $plugin['title'];
    }

    $form['plugin'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Select plug-in'),
      '#options' => $plugins_data,
      '#required' => TRUE,
      '#default_value' => $entity_limit->getPlugin(),
    );

    $entity_types = $this->getContentEntities();
    $form['entity_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Select entity type'),
      '#options' => $entity_types,
      '#required' => TRUE,
      '#default_value' => $entity_limit->getEntityLimitType(),
      '#ajax' => array(
        'callback' => '::entityBundleCallback',
        'wrapper' => 'bundles-container',
      ),
    );

    $entity_type = $form_state->getValue('entity_type') ? $form_state->getValue('entity_type') : $entity_limit->getEntityLimitType();
    $options = array();
    if ($entity_type) {
      $bundles = $this->entityManager->getBundleInfo($entity_type);
      if (!empty($bundles)) {
        foreach ($bundles as $machine_name => $bundle) {
          $options[$machine_name] = $bundle['label'];
        }
        $form['entity_bundles'] = array(
          '#type' => 'checkboxes',
          '#required' => TRUE,
          '#title' => $this->t('Select @entity_type bundles', array('@entity_type' => $entity_types[$entity_type])),
          '#description' => $this->t('Select bundles to apply limit.'),
          '#options' => $options,
        );
        if($default_value = $entity_limit->getEntityLimitBundles()){
          $form['entity_bundles']['#default_value'] = $default_value;
        }
      }
    }
    $form['entity_bundles']['#prefix'] = '<div id="bundles-container">';
    $form['entity_bundles']['#suffix'] = '</div>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save entity limit');
    if ($form_state->getFormObject()->getEntity()->isNew()) {
      $actions['submit']['#value'] = $this->t('Save and manage limits');
    }
    $actions['delete']['#value'] = $this->t('Delete entity limit');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = trim($form_state->getValue('id'));
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName('id', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", array('%invalid' => $id)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity_limit = $this->entity;
    $status = $entity_limit->save();

    $args = array('%label' => $entity_limit->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The entity limit %label has been updated.', $args));
      $form_state->setRedirectUrl($entity_limit->urlInfo('collection'));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message($this->t('The entity limit %label has been added.', $args));
      $context = array_merge($args, array('link' => $entity_limit->link($this->t('View'), 'collection')));
      $this->logger('node')->notice('Added entity limit %name.', $context);
      $form_state->setRedirectUrl($entity_limit->urlInfo('manage-form'));
    }
  }

  /**
   * AJAX Callback to add bundle list based on the selected entity type.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Array.
   */
  public function entityBundleCallback(array &$form, FormStateInterface &$form_state) {
    return $form['entity_bundles'];
  }

  /**
   * Get list of all content entities.
   *
   * @return array
   *   Array of content entities.
   */
  protected function getContentEntities() {
    $entity_manager = $this->entityManager->getEntityTypeLabels(TRUE);
    $content_entities = array_values($entity_manager['Content']);
    $content_entities_key = array_keys($entity_manager['Content']);
    $content_entities = array_combine($content_entities_key, $content_entities);
    return $content_entities;
  }

}
