<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

/**
 * Defines an interface for wrapped entities with a published state.
 *
 * @see \Drupal\Core\Entity\EntityPublishedInterface
 */
interface PublishedInterface {

  /**
   * Determine if the wrapped entity is published.
   *
   * @return boolean
   *   Returns true if the entity is published or false otherwise.
   */
  public function isPublished(): bool;

}
