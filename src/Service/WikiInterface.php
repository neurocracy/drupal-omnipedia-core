<?php

namespace Drupal\omnipedia_core\Service;

/**
 * The Omnipedia wiki service interface.
 */
interface WikiInterface {

  /**
   * Determine if a parameter is or equates to a wiki node.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @return boolean
   *   Returns true if the $node parameter is a wiki node or if it is a numeric
   *   value that equates to the ID of a wiki node; returns false otherwise.
   */
  public function isWikiNode($node): bool;

  /**
   * Get a wiki node from the passed parameter, if possible.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Returns the node object if $node a wiki node; if $node is a node but not
   *   a wiki node, returns null; if $node is a numeric value that doesn't
   *   equate to a wiki node's ID, returns null.
   */
  public function getWikiNode($node);

  /**
   * Get the date value from a provided wiki node, if possible.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @return string|null
   *   Returns the string date of $node if it is a wiki node; returns null in
   *   all other cases.
   */
  public function getWikiNodeDate($node);

}
