<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

use Drupal\typed_entity\WrappedEntities\WrappedEntityBase as DefaultWrappedEntityBase;
use Drupal\omnipedia_core\WrappedEntities\WrappedEntityInterface;

/**
 * Wrapped entity base class extended with additional methods.
 *
 * @see \Drupal\typed_entity\WrappedEntities\WrappedEntityBase
 */
abstract class WrappedEntityBase extends DefaultWrappedEntityBase implements WrappedEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return $this->getEntity()->id();
  }

}
