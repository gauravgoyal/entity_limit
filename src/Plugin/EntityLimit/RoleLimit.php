<?php

namespace Drupal\entity_limit\Plugin\EntityLimit;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_limit\Entity\EntityLimit;
use Drupal\entity_limit\EntityLimitInspector;
use Drupal\entity_limit\Plugin\EntityLimitPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a plugin to limit entities per role.
 *
 * @EntityLimit(
 *   id = "role_limit",
 *   title = @Translation("Role Limit"),
 *   priority = 1,
 * )
 */
class RoleLimit extends EntityLimitPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Session\AccountInterface $account
   *   User Account object.
   */
  protected $account;

  /**
   * RoleLimit constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin Definition.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User Account.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // TODO: Implement create() method.
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;
    $entity_limit = $form['entity_limit'];
    $limits = $entity_limit->get('limits');
    if (is_null($form_state->get('num_rows'))) {
      $count = !empty($limits) ? count($limits) : 1;
      $form_state->set('num_rows', $count);
    }
    else {
      $count = $form_state->get('num_rows');
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

    for ($i = 0; $i < $count; $i++) {
      $form['limits'][$i]['id'] = array(
        '#type' => 'select',
        '#description' => $this->t('Limit will be applied to this role'),
        '#options' => $allowed_roles,
        '#required' => TRUE,
        '#default_value' => isset($limits[$i]['id']) ? $limits[$i]['id'] : '',
      );

      $form['limits'][$i]['limit'] = array(
        '#type' => 'textfield',
        '#description' => $this->t('Add limit applicable for this user. Use -1 for unlimited limits.'),
        '#size' => 60,
        '#required' => TRUE,
        '#default_value' => isset($limits[$i]['limit']) ? $limits[$i]['limit'] : '',
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
   * Get applicable limit count for account based on entity_limit.
   *
   * @param \Drupal\entity_limit\Entity\EntityLimit $entityLimit
   *   Entity Limit Object.
   * @param \Drupal\Core\Session\AccountInterface|NULL $account
   *   Optional User account.
   *
   * @return mixed
   *   Limit according to role.
   */
  public function getLimitCount(EntityLimit $entityLimit, AccountInterface $account = NULL) {
    if ($account) {
      $account_roles = $account->getRoles();
    }
    else {
      $account_roles = $this->account->getRoles();
    }

    $entity_limits = [];
    foreach ($entityLimit->get('limits') as $limit) {
      $entity_limits[$limit['id']] = $limit['limit'];
    }

    // Get Lowest Limit.
    $limit = 0;

    // If a user has multiple roles, then take the highest limit from them.
    foreach ($account_roles as $role) {
      $temp = (isset($entity_limits[$role])) ? $entity_limits[$role] : NULL;

      // @todo Remove this check outside of plugin.
      if ($temp === EntityLimitInspector::ENTITYLIMITNOLIMIT) {
        return $temp;
      }

      $limit = ($temp > $limit) ? $temp : $limit;
    }

    return $limit;
  }

  /**
   * Compare limits and provide access.
   *
   * @param int $limit
   *   The limit.
   * @param \Drupal\entity_limit\Entity\EntityLimit $entityLimit
   *   The entity limit.
   * @param \Drupal\Core\Session\AccountInterface|NULL $account
   *   Optional User account.
   *
   * @return bool
   *   TRUE|FALSE for access.
   */
  public function checkAccess($limit, EntityLimit $entityLimit, AccountInterface $account = NULL) {
    if ($account) {
      $uid = $account->id();
    }
    else {
      $uid = $this->account->id();
    }

    $access = TRUE;
    $query = \Drupal::entityQuery($entityLimit->getEntityLimitType());
    $query->condition('type', $entityLimit->getEntityLimitBundles(), 'IN');
    $query->condition('uid', $uid);
    $count = count($query->execute());
    if ($count >= (int) $limit) {
      $access = FALSE;
    }
    return $access;
  }

}
