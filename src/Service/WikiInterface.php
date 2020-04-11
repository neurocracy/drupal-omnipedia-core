<?php

namespace Drupal\omnipedia_core\Service;

/**
 * The Omnipedia wiki service interface.
 */
interface WikiInterface {

  /**
   * Get the wiki node type.
   *
   * @return string
   *   The machine name of the wiki node type.
   */
  public function getWikiNodeType(): string;

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
   * Get the wiki node date field name.
   *
   * @return string
   *   The machine name of the wiki node date field.
   */
  public function getWikiNodeDateFieldName(): string;

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

  /**
   * Get tracked wiki node data.
   *
   * @return array
   *   An array with saved wiki node data, with the following top level keys:
   *
   *   - nodes: an array with keys corresponding to node IDs, with each value
   *     containing an array with the following:
   *
   *     - 'date': the node's date as a string in 'storage' format
   *
   *     - 'title': string title of the node
   *
   *     - 'published': boolean indicating whether this node is published
   *
   *   - dates: an array with dates in the 'storage' format as the keys, each
   *     containing an array of node IDs
   *
   *   - titles: an array whose keys are node IDs and values are their titles
   */
  public function getTrackedWikiNodeData(): array;

  /**
   * Start tracking or update tracking of a wiki node.
   *
   * This saves data about a wiki node to be quickly accessed later when loading
   * the full node would add unnecessary overhead from a performance point of
   * view.
   *
   * This should be called when a wiki node is created or updated.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @see $this->untrackWikiNode()
   *   Stops tracking a wiki node.
   */
  public function trackWikiNode($node): void;

  /**
   * Stop tracking a wiki node.
   *
   * This saves data about a wiki node to be quickly accessed later when loading
   * the full node would add unnecessary overhead from a performance point of
   * view.
   *
   * This should be called when a wiki node is deleted.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @see $this->trackWikiNode()
   *   Starts tracking or updates tracking of a wiki node.
   */
  public function untrackWikiNode($node): void;

}
