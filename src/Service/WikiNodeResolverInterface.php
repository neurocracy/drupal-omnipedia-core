<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;

/**
 * The Omnipedia wiki node resolver service interface.
 */
interface WikiNodeResolverInterface {

  /**
   * Resolve a provided parameter to a node, loading it if need be.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing nid to load.
   *
   * @return \Drupal\omnipedia_core\Entity\NodeInterface|null
   *   Either a node object, or null if one cannot be loaded.
   */
  public function resolveNode($node): ?WikiNodeInterface;

  /**
   * Resolve a node or title to all nids with the same title.
   *
   * @param \Drupal\node\NodeInterface|int|string $nodeOrTitle
   *   Must be one of the following:
   *
   *   - An instance of \Drupal\node\NodeInterface, i.e. a node object.
   *
   *   - An integer or a numeric string that equates to an nid.
   *
   *   - A non-numeric string which is assumed to be a node title to search for.
   *
   * @return array
   *   An array containing zero or more nids as values.
   */
  public function nodeOrTitleToNids($nodeOrTitle): array;

}
