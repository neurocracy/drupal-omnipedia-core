<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\omnipedia_core\Entity\NodeInterface;

/**
 * The Omnipedia wiki node viewed service interface.
 */
interface WikiNodeViewedInterface {

  /**
   * Add a wiki node to a user's recently viewed session.
   *
   * @param \Drupal\omnipedia_core\Entity\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID (nid) to load.
   *
   * @see \Drupal\omnipedia_menu\Controller\RandomPageController::view()
   *   Used by this to avoid choosing a recently viewed wiki node.
   */
  public function addNode(NodeInterface|int|string $node): void;

  /**
   * Get the most recent wiki nodes viewed by the current user, if any.
   *
   * @return array
   *   An array of nids, or an empty array if no recent wiki nodes were found in
   *   the user's session.
   *
   * @see self::addNode()
   *   Nodes are added to the user's session via this method.
   */
  public function getNodes(): array;

}
