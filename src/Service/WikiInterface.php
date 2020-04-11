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

  /**
   * Get all revisions of a wiki node.
   *
   * Note that this does not do any access checking, so code that calls this is
   * responsible for not displaying information about nodes the user does not
   * have access to. For an example of how to accomplish this, see
   * \Drupal\omnipedia_core\Plugin\Block\PageRevisionHistory::build().
   *
   * @param \Drupal\node\NodeInterface|int|string $nodeOrTitle
   *   Must be one of the following:
   *
   *   - An instance of \Drupal\node\NodeInterface, i.e. a node object
   *
   *   - An integer or a numeric string that equates to a node ID
   *
   *   - A non-numeric string which is assumed to be a node title to search for
   *
   * @return array
   *   Either an array of wiki node data, ordered by their date, or an empty
   *   array if no matches could be found. Each array index contains an array
   *   with the following keys:
   *
   *   - 'nid': the node ID as an integer
   *
   *   - 'date': the node's date as a string
   *
   *   - 'title': the node's title as a string
   *
   *   - 'published': boolean indicating if the node is published
   *
   *   If the $nodeOrTitle parameter is a node but not a wiki node, or if it is
   *   a title and no nodes could be found with that title, an empty array will
   *   be returned.
   *
   * @throws \InvalidArgumentException
   *   Exception thrown if the $nodeOrTitle parameter is not one of the expected
   *   values.
   *
   * @todo Should this perform access checking?
   */
  public function getWikiNodeRevisions($nodeOrTitle): array;

}
