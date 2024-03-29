<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\node\NodeInterface;

/**
 * The Omnipedia wiki node resolver service interface.
 */
interface WikiNodeResolverInterface {

  /**
   * Resolve a provided parameter to a node, loading it if need be.
   *
   * @param mixed $node
   *   A node entity object or a numeric value (integer or string) that equates
   *   to an existing node ID (nid) to load. Any other value will return null.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Either a node object, or null if one cannot be loaded.
   */
  public function resolveNode(mixed $node): ?NodeInterface;

  /**
   * Resolve a node or title to all nids with the same title.
   *
   * @param \Drupal\node\NodeInterface|int|string $nodeOrTitle
   *   Must be one of the following:
   *
   *   - An instance of \Drupal\node\NodeInterface, i.e. a node
   *     object.
   *
   *   - An integer or a numeric string that equates to an nid.
   *
   *   - A non-numeric string which is assumed to be a node title to search for.
   *
   * @return array
   *   An array containing zero or more nids as values.
   */
  public function nodeOrTitleToNids(
    NodeInterface|int|string $nodeOrTitle
  ): array;

  /**
   * Determine if a parameter is or equates to a wiki node.
   *
   * @param mixed $node
   *   A node entity object or a numeric value (integer or string) that equates
   *   to an existing node ID (nid) to load. Any other value will return false.
   *
   * @return boolean
   *   Returns true if the $node parameter is a wiki node or if it is a numeric
   *   value that equates to the ID of a wiki node; returns false otherwise.
   */
  public function isWikiNode(mixed $node): bool;

  /**
   * Resolve a provided parameter to a wiki node, loading it if need be.
   *
   * @param mixed $node
   *   A node entity object or a numeric value (integer or string) that equates
   *   to an existing node ID (nid) to load. Any other value will return null.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Returns the node object if $node is a wiki node; if $node is a node but
   *   not a wiki node, returns null; if $node is a numeric value that doesn't
   *   equate to a wiki node's ID, returns null.
   */
  public function resolveWikiNode(mixed $node): ?NodeInterface;

}
