<?php

namespace Drupal\omnipedia_core\Entity;

use Drupal\node\Entity\Node as CoreNode;
use Drupal\node\NodeInterface as CoreNodeInterface;
use Drupal\omnipedia_core\Entity\NodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\Service\WikiNodeViewedInterface;

/**
 * Omnipedia node entity class.
 *
 * This extends the Drupal core node class to add methods to interact with wiki
 * nodes.
 *
 * @see \Drupal\node\Entity\Node
 *   Drupal core node class.
 *
 * @see https://www.drupal.org/node/3191609
 *   Drupal core 9.3 added support for per-bundle entity classes.
 *
 * @todo Refactor this as a bundle entity class which is supported as of Drupal
 *   core 9.3.
 */
class Node extends CoreNode implements NodeInterface {

  /**
   * The wiki node type.
   */
  protected const WIKI_NODE_TYPE = 'wiki_page';

  /**
   * The name of the date field on wiki nodes.
   */
  protected const WIKI_NODE_DATE_FIELD = 'field_date';

  /**
   * The name of the hide from search flag field on wiki nodes.
   */
  protected const WIKI_NODE_HIDDEN_FROM_SEARCH_FIELD = 'field_hide_from_search';

  /**
   * The Omnipedia wiki node main page service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface
   */
  protected $wikiNodeMainPage;

  /**
   * The Omnipedia wiki node revision service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface
   */
  protected $wikiNodeRevision;

  /**
   * The Omnipedia wiki node viewed service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeViewedInterface
   */
  protected $wikiNodeViewed;

  /**
   * This wiki node's previous revision, if one exists.
   *
   * This begins as false, and is populated with a wiki node if the current
   * node is a wiki node and it has a previous revision. If the current node is
   * not a wiki node or it is but has no previous revision, this is set to null.
   *
   * @var boolean|null|\Drupal\omnipedia_core\Entity\NodeInterface
   *
   * @see $this->getPreviousWikiNodeRevision()
   */
  protected $previousWikiNodeRevision = false;

  /**
   * {@inheritdoc}
   */
  public function injectWikiDependencies(
    WikiNodeMainPageInterface $wikiNodeMainPage,
    WikiNodeRevisionInterface $wikiNodeRevision,
    WikiNodeViewedInterface   $wikiNodeViewed
  ): void {
    // Save dependencies.
    $this->wikiNodeMainPage = $wikiNodeMainPage;
    $this->wikiNodeRevision = $wikiNodeRevision;
    $this->wikiNodeViewed   = $wikiNodeViewed;
  }

  /**
   * {@inheritdoc}
   */
  public static function getWikiNodeType(): string {
    return self::WIKI_NODE_TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function isWikiNode(): bool {
    return $this->getType() === self::WIKI_NODE_TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getWikiNodeDateFieldName(): string {
    return self::WIKI_NODE_DATE_FIELD;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeDate(): ?string {
    if ($this->isWikiNode()) {
      return $this->get(self::WIKI_NODE_DATE_FIELD)[0]->value;
    } else {
      return null;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeRevisions(): array {
    return $this->wikiNodeRevision->getWikiNodeRevisions($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeRevision(string $date): ?NodeInterface {
    return $this->wikiNodeRevision->getWikiNodeRevision($this, $date);
  }

  /**
   * {@inheritdoc}
   */
  public function hasPreviousWikiNodeRevision(): bool {
    return \is_object($this->getPreviousWikiNodeRevision());
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousWikiNodeRevision(): ?NodeInterface {

    if ($this->previousWikiNodeRevision !== false) {
      return $this->previousWikiNodeRevision;
    }

    // Bail if this is not a wiki node.
    if (!$this->isWikiNode()) {
      $this->previousWikiNodeRevision = null;

      return $this->previousWikiNodeRevision;
    }

    /** @var array */
    $revisions = $this->getWikiNodeRevisions();

    /** @var int */
    $nid = (int) $this->nid->getString();

    // If there's only one revision or this node is the first revision, there is
    // no previous revision.
    if (
      count($revisions) === 1 ||
      \reset($revisions)['nid'] === $nid
    ) {
      $this->previousWikiNodeRevision = null;

      return $this->previousWikiNodeRevision;
    }

    foreach ($revisions as $data) {
      if ($data['nid'] !== $nid) {
        \next($revisions);

        continue;
      }

      /** @var array */
      $previousNodeData = \prev($revisions);

      $this->previousWikiNodeRevision = $this->getWikiNodeRevision(
        $previousNodeData['date']
      );

      return $this->previousWikiNodeRevision;
    }

    $this->previousWikiNodeRevision = null;

    return $this->previousWikiNodeRevision;

  }

  /**
   * {@inheritdoc}
   */
  public function isMainPage(): bool {
    return $this->wikiNodeMainPage->isMainPage($this);
  }

  /**
   * {@inheritdoc}
   */
  public function isHiddenFromSearch(): bool {
    if ($this->isWikiNode()) {
      // Main pages are always hidden from search.
      if ($this->isMainPage()) {
        return true;
      } else {
        return $this->get(self::WIKI_NODE_HIDDEN_FROM_SEARCH_FIELD)[0]->value;
      }
    } else {
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addRecentlyViewedWikiNode(): void {
    $this->wikiNodeViewed->addNode($this);
  }

}
