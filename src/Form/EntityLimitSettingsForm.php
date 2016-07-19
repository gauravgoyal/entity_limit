<?php

namespace Drupal\entity_limit\Form;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Form.
 */
class EntityLimitSettingsForm extends ConfigFormBase {

  protected $entityManager;

  protected $configFactory;

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
  public function getFormId() {
    return 'entity_limit_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'entity_limit.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = array_pop($this->getEditableConfigNames());
    $config = $this->config($config);

    $entity_manager = $this->entityManager->getEntityTypeLabels(TRUE);
    $content_entities = array_values($entity_manager['Content']);
    $content_entities_key = array_keys($entity_manager['Content']);
    $content_entities = array_combine($content_entities_key, $content_entities);

    $form['allowed_entities'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable Entities for limiting'),
      '#description' => $this->t('Tick all the entities for which you want to enable limit'),
      '#options' => $content_entities,
      '#default_value' => $config->get('allowed_entities'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = array_pop($this->getEditableConfigNames());
    $config = $this->config($config);
    $allowed_entities = $form_state->getValue('allowed_entities');
    $config->set('allowed_entities', $allowed_entities)->save();
    return parent::submitForm($form, $form_state);
  }

}
