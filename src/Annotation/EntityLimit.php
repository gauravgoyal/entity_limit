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
   * The human-readable name of the filter.
   *
   * This is used as an administrative summary of what the filter does.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

}
