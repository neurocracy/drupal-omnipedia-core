<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\node\NodeInterface;

/**
 * The Omnipedia wiki node tracker service interface.
 */
interface WikiNodeTrackerInterface {

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
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::trackWikiNode()
   *   Starts tracking or updates tracking of a wiki node.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::untrackWikiNode()
   *   Stops tracking a wiki node.
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
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @param string $nodeDate
   *   The providded node's date as a string in the 'storage' format.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::getTrackedWikiNodeData()
   *   Returns tracked wiki node data.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::untrackWikiNode()
   *   Stops tracking a wiki node.
   */
  public function trackWikiNode(NodeInterface $node, string $nodeDate): void;

  /**
   * Stop tracking a wiki node.
   *
   * This saves data about a wiki node to be quickly accessed later when loading
   * the full node would add unnecessary overhead from a performance point of
   * view.
   *
   * This should be called when a wiki node is deleted.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::getTrackedWikiNodeData()
   *   Returns tracked wiki node data.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::trackWikiNode()
   *   Starts tracking or updates tracking of a wiki node.
   */
  public function untrackWikiNode(NodeInterface $node): void;

}
