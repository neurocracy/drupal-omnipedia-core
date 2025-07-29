<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface as DefaultWrappedEntityInterface;

/**
 * Wrapped entity base interface extended with additional methods.
 *
 * @see \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface
 */
interface WrappedEntityInterface extends DefaultWrappedEntityInterface {

  /**
   * Get the wrapped entity identifier.
   *
   * @return string
   *   The wrapped identifier as a string.
   */
  public function id(): string;

}
