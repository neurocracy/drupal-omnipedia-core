<?php

namespace Drupal\omnipedia_core\Entity;

use Drupal\node\Entity\Node as CoreNode;
use Drupal\node\NodeInterface as CoreNodeInterface;
use Drupal\omnipedia_core\Entity\NodeInterface;

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

}
