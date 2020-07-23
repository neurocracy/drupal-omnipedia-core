<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;

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
  public function getWikiNodeDate($node): ?string;

  /**
   * Get tracked wiki node data.
   *
   * @return array
   *   An array with saved wiki node data. See \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::getTrackedWikiNodeData()
   *   for structure.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::getTrackedWikiNodeData()
   *   Returns tracked wiki node data.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::getTrackedWikiNodeData()
   *   Describes returned array structure.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::trackWikiNode()
   *   Starts tracking or updates tracking of a wiki node.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::untrackWikiNode()
   *   Stops tracking a wiki node.
   */
  public function getTrackedWikiNodeData(): array;

  /**
   * Start tracking or update tracking of a wiki node.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::getTrackedWikiNodeData()
   *   Returns tracked wiki node data.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::untrackWikiNode()
   *   Stops tracking a wiki node.
   */
  public function trackWikiNode($node): void;

  /**
   * Stop tracking a wiki node.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::getTrackedWikiNodeData()
   *   Returns tracked wiki node data.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::trackWikiNode()
   *   Starts tracking or updates tracking of a wiki node.
   */
  public function untrackWikiNode($node): void;

  /**
   * Add a wiki node to a user's recently viewed session.
   *
   * This is used in self::getRandomWikiNodeRouteParameters() to avoid choosing
   * a recently viewed wiki node.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   */
  public function addRecentlyViewedWikiNode($node): void;

  /**
   * Get route parameters for a random wiki node.
   *
   * @param string $date
   *   A date string in the format stored in a wiki node's date field to limit
   *   the randomization to. Only a node with this date will be returned.
   *
   * @return array
   *   An array with a 'node' key that contains an nid for the randomly chosen
   *   wiki node that has the same date as that which is provided, is published,
   *   is not a main page, and has not been viewed recently by the current user.
   */
  public function getRandomWikiNodeRouteParameters(string $date): array;

}
