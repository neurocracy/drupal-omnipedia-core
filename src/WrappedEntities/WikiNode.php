<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\omnipedia_core\WrappedEntities\Node;
use Drupal\typed_entity\TypedEntityContext;

/**
 * Wraps the wiki node entity.
 */
class WikiNode extends Node {

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {

    return $context->offsetGet('entity')->getType() === WikiNodeInfo::TYPE;

  }
  /**
   * {@inheritdoc}
   */
  public function isWikiNode(): bool {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiDate(): ?string {

    return $this->getEntity()->get(WikiNodeInfo::DATE_FIELD)->getString();

  }

}
