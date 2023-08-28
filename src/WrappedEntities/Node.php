<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityBase;

/**
 * Wraps the Node entity.
 */
class Node extends WrappedEntityBase implements NodeWithWikiInfoInterface {

  /**
   * {@inheritdoc}
   */
  public function isWikiNode(): bool {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiDate(): ?string {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiRevisions(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiRevision(string $date): ?NodeWithWikiInfoInterface {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousWikiRevision(): ?NodeWithWikiInfoInterface {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPreviousWikiRevision(): bool {
    return false;
  }

}
