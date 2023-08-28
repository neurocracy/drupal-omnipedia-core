<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

/**
 * Defines an interface for wrapped node entities with wiki information.
 */
interface NodeWithWikiInfoInterface {

  /**
   * Determine if the wrapped node is a wiki node.
   *
   * @return boolean
   *   Returns true if the node is a wiki node or false otherwise.
   */
  public function isWikiNode(): bool;

  /**
   * Get the date field value from a wrapped wiki node.
   *
   * @return string|null
   *   Returns the string date of the wrapped node's date field if it is a wiki
   *   node or null otherwise.
   */
  public function getWikiDate(): ?string;

}
