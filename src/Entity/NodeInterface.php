<?php

namespace Drupal\omnipedia_core\Entity;

use Drupal\node\NodeInterface as CoreNodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\Service\WikiNodeViewedInterface;

/**
 * Omnipedia node entity interface.
 */
interface NodeInterface {

  /**
   * Inject dependencies.
   *
   * As of Drupal core 8.9, it's not possible to easily inject any services into
   * Node objects (or any entities for that matter), as a "create" method
   * already exists for a different purpose. Even the core Node object uses the
   * \Drupal static class for any services. Because of these issues, one work-
   * around is to use a custom method that's called by a Node storage class when
   * a node is loaded from storage, which is what this method accomplishes.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface $wikiNodeMainPage
   *   The Omnipedia wiki node main page service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface $wikiNodeRevision
   *   The Omnipedia wiki node revision service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeViewedInterface $wikiNodeViewed
   *   The Omnipedia wiki node viewed service.
   *
   * @see https://www.drupal.org/project/drupal/issues/2142515
   *   Drupal core issue to add dependency injection support to entities.
   */
  public function injectWikiDependencies(
    WikiNodeMainPageInterface $wikiNodeMainPage,
    WikiNodeRevisionInterface $wikiNodeRevision,
    WikiNodeViewedInterface   $wikiNodeViewed
  ): void;

  /**
   * Get the wiki node type.
   *
   * This is a static method so that it can be used without needing to
   * instantiate a Node object.
   *
   * @return string
   *   The machine name of the wiki node type.
   */
  public static function getWikiNodeType(): string;

  /**
   * Determine if this node is a wiki node.
   *
   * @return boolean
   *   Returns true if the node is a wiki node or false otherwise.
   */
  public function isWikiNode(): bool;

  /**
   * Get the wiki node date field name.
   *
   * This is a static method so that it can be used without needing to
   * instantiate a Node object.
   *
   * @return string
   *   The machine name of the wiki node date field.
   */
  public static function getWikiNodeDateFieldName(): string;

  /**
   * Get the date field value from this wiki node.
   *
   * @return string|null
   *   Returns the string date of this node's date field if it is a wiki node
   *   or null otherwise.
   */
  public function getWikiNodeDate(): ?string;

  /**
   * Get all revisions of this wiki node.
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
  public function getWikiNodeRevisions(): array;

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
   * @return \Drupal\omnipedia_core\Entity\NodeInterface|null
   *   Returns the node object if this is wiki node or null if this is not a
   *   wiki node.
   */
  public function getWikiNodeRevision(string $date): ?NodeInterface;

  /**
   * Whether this wiki node has a previous revision.
   *
   * @return boolean
   *   True if there is a previous revision or false otherwise.
   */
  public function hasPreviousWikiNodeRevision(): bool;

  /**
   * Get the previous wiki node revision of this node if one exists.
   *
   * Note that this doesn't do any access checking, so code that calls this is
   * responsible for not displaying information about nodes the user does not
   * have access to. For an example of how to accomplish this, see
   * \Drupal\omnipedia_block\Plugin\Block\PageRevisionHistory::build().
   *
   * @return \Drupal\omnipedia_core\Entity\NodeInterface|null
   *   The node object of the previous revision if it exists or null otherwise.
   */
  public function getPreviousWikiNodeRevision(): ?NodeInterface;

  /**
   * Determine if this node is a main page wiki node.
   *
   * @return boolean
   *   Returns true if this node is a main page wiki node or false otherwise.
   */
  public function isMainPage(): bool;

  /**
   * Whether this wiki node should be hidden from search results.
   *
   * This will return true in the following conditions:
   *
   * - This is a wiki node with the hide from search field set to true.
   *
   * - This is a wiki node that's also a main page; this will always return
   *   true, ignoring the hide from search field.
   *
   * @return boolean
   *   True if this wiki node should be hidden from search results, false
   *   otherwise.
   */
  public function isHiddenFromSearch(): bool;

  /**
   * Add this wiki node to a user's recently viewed session.
   *
   * @see \Drupal\omnipedia_menu\Controller\RandomPageController::view()
   *   Used by this to avoid choosing a recently viewed wiki node.
   */
  public function addRecentlyViewedWikiNode(): void;

}
