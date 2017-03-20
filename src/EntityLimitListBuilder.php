<?php

namespace Drupal\entity_limit;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of Entity Limit entities.
 */
class EntityLimitListBuilder extends ConfigEntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Entity Limit');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $account = \Drupal::currentUser();
    if ($account->hasPermission('manage entity limits')) {
      $operations['manage-limits'] = array(
        'title' => t('Manage limits'),
        'weight' => 15,
        'url' => Url::fromRoute("entity.entity_limit.manage_form", array(
          $entity->getEntityTypeId() => $entity->id(),
        )),
      );
    }
    if (isset($operations['edit'])) {
      $operations['edit']['weight'] = 30;
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('No entity limits available. <a href=":link">Add entity limit</a>.', [
      ':link' => Url::fromRoute('entity.entity_limit.add')->toString()
    ]);
    return $build;
  }

}
