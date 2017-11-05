<?php

namespace Drupal\entity_limit\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a entity limit violations plugin annotation object.
 *
 * @Annotation
 */
class EntityLimit extends Plugin {
  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the entity limit plugin.
   *
   * This is used as an administrative title for the entity limit plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

  /**
   * Priority for the entity limit plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $priority;

}
