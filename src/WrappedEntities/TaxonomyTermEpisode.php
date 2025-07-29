<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

use Drupal\omnipedia_core\Entity\TaxonomyTermInfo;
use Drupal\omnipedia_core\WrappedEntities\TaxonomyTerm;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity\TypedEntityContext;

/**
 * Wraps episode taxonomy term entities.
 */
class TaxonomyTermEpisode extends TaxonomyTerm {

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {

    return (
      $context->offsetGet(
        'entity',
      )->bundle() === TaxonomyTermInfo::EPISODE_VOCABULARY &&
      parent::applies($context)
    );

  }

  /**
   * {@inheritdoc}
   */
  public function getDiscoursePermalinkSlug(): ?string {

    return $this->getEntity()->get(
      TaxonomyTermInfo::DISCOURSE_PERMALINK_FIELD,
    )->first()->getString();

  }

}
