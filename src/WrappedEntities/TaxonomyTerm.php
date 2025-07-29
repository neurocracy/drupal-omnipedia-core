<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

use Drupal\omnipedia_core\WrappedEntities\TaxonomyTermWithWikiInfoInterface;
use Drupal\omnipedia_core\WrappedEntities\WrappedEntityBase;
use Drupal\taxonomy\TermInterface;
use Drupal\typed_entity\TypedEntityContext;

/**
 * Wraps taxonomy term entities not wrapped by more specific variants.
 */
class TaxonomyTerm extends WrappedEntityBase implements TaxonomyTermWithWikiInfoInterface {

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {
    return $context->offsetGet('entity') instanceof TermInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function getDiscoursePermalinkSlug(): ?string {
    return null;
  }

}
