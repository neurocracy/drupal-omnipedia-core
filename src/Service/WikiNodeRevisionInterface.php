<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\node\NodeInterface;

/**
 * The Omnipedia wiki node revision service interface.
 */
interface WikiNodeRevisionInterface {

  /**
   * Get all revisions of a wiki node.
   *
   * Note that this does not do any access checking, so code that calls this is
   * responsible for not displaying information about nodes the user does not
   * have access to. For an example of how to accomplish this, see
   * \Drupal\omnipedia_block\Plugin\Block\PageRevisionHistory::build().
   *
   * @param \Drupal\node\NodeInterface|int|string $nodeOrTitle
   *   Must be one of the following:
   *
   *   - An instance of \Drupal\node\NodeInterface, i.e. a node
   *     object.
   *
   *   - An integer or a numeric string that equates to a node ID (nid).
   *
   *   - A non-numeric string which is assumed to be a node title to search for.
   *
   * @return array
   *   Either an array of wiki node data, ordered by their date, or an empty
   *   array if no matches could be found. Each array index contains an array
   *   with the following keys:
   *
   *   - 'nid': the node ID as an integer.
   *
   *   - 'date': the node's date as a string.
   *
   *   - 'title': the node's title as a string.
   *
   *   - 'published': boolean indicating if the node is published.
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
  public function getWikiNodeRevisions(
    NodeInterface|int|string $nodeOrTitle
  ): array;

  /**
   * Get a wiki node's revision for a specified date.
   *
   * Note that this does not do any access checking, so code that calls this is
   * responsible for not displaying information about nodes the user does not
   * have access to. For an example of how to accomplish this, see
   * \Drupal\omnipedia_block\Plugin\Block\PageRevisionHistory::build().
   *
   * @param \Drupal\node\NodeInterface|int|string $nodeOrTitle
   *   Must be one of the following:
   *
   *   - An instance of \Drupal\node\NodeInterface, i.e. a node
   *     object.
   *
   *   - An integer or a numeric string that equates to an node ID (nid).
   *
   *   - A non-numeric string which is assumed to be a node title to search for.
   *
   * @param string $date
   *   A date string in the format stored in a wiki node's date field.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Returns the node object if $nodeOrTitle can be resolved to a wiki node;
   *   if $nodeOrTitle cannot be resolved to a wiki node, returns null; if
   *   $nodeOrTitle is a numeric value that doesn't equate to a wiki node's ID,
   *   returns null.
   *
   * @throws \InvalidArgumentException
   *   Exception thrown if the $nodeOrTitle parameter is not one of the expected
   *   values.
   */
  public function getWikiNodeRevision(
    NodeInterface|int|string $nodeOrTitle, string $date
  ): ?NodeInterface;

  /**
   * Get the previous wiki node revision of a provided node if one exists.
   *
   * Note that this does not do any access checking, so code that calls this is
   * responsible for not displaying information about nodes the user does not
   * have access to. For an example of how to accomplish this, see
   * \Drupal\omnipedia_block\Plugin\Block\PageRevisionHistory::build().
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node object of the previous revision if it exists or null otherwise.
   */
  public function getPreviousRevision(NodeInterface $node): ?NodeInterface;

  /**
   * Whether a provided wiki node has a previous revision.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @return boolean
   *   True if there is a previous revision or false otherwise.
   */
  public function hasPreviousRevision(NodeInterface $node): bool;

}
