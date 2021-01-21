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
 * nodes. Since Drupal core does not yet support per-bundle entity classes as of
 * 8.9, this is used for all node types.
 *
 * @see \Drupal\node\Entity\Node
 *   Drupal core node class.
 *
 * @see https://www.drupal.org/project/drupal/issues/2570593
 *   Drupal core issue to add support for per-bundle entity classes.
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
