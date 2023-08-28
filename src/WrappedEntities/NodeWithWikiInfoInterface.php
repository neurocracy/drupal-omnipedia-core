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

  /**
   * Get all revisions of the wrapped wiki node.
   *
   * Note that this does not do any access checking, so code that calls this is
   * responsible for not displaying information about nodes the user does not
   * have access to. For an example of how to accomplish this, see
   * \Drupal\omnipedia_block\Plugin\Block\PageRevisionHistory::build().
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
   *   If this node is not a wiki node, an empty array will be returned.
   *
   * @todo Should this perform access checking?
   */
  public function getWikiRevisions(): array;

  /**
   * Get this wiki node's revision for a specified date.
   *
   * Note that this does not do any access checking, so code that calls this is
   * responsible for not displaying information about nodes the user does not
   * have access to. For an example of how to accomplish this, see
   * \Drupal\omnipedia_block\Plugin\Block\PageRevisionHistory::build().
   *
   * @param string $date
   *   A date string in the format stored in a wiki node's date field.
   *
   * @return self|null
   *   Returns the wrapped node entity if this is wiki node or null if this is
   *   not a wiki node.
   */
  public function getWikiRevision(string $date): ?self;

  /**
   * Get the previous wiki node revision of this node if one exists.
   *
   * Note that this doesn't do any access checking, so code that calls this is
   * responsible for not displaying information about nodes the user does not
   * have access to. For an example of how to accomplish this, see
   * \Drupal\omnipedia_block\Plugin\Block\PageRevisionHistory::build().
   *
   * @return self|null
   *   The typed entity wrapper of the previous revision if it exists or null
   *   otherwise.
   */
  public function getPreviousWikiRevision(): ?self;

  /**
   * Whether this wiki node has a previous revision.
   *
   * @return boolean
   *   True if there is a previous revision or false otherwise.
   */
  public function hasPreviousWikiRevision(): bool;

}
