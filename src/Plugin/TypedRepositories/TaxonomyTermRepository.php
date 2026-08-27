<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Plugin\TypedRepositories;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\omnipedia_core\WrappedEntities\TaxonomyTerm;
use Drupal\omnipedia_core\WrappedEntities\TaxonomyTermEpisode;
use Drupal\typed_entity\Attribute\TypedRepository;
use Drupal\typed_entity\ClassWithVariants;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for wrapped taxonomy term entities.
 */
#[TypedRepository(
  entity_type_id: 'taxonomy_term',
  wrappers: new ClassWithVariants(
    fallback: TaxonomyTerm::class,
    variants: [
      TaxonomyTermEpisode::class,
    ],
  ),
  description: new TranslatableMarkup(
    'The repository for taxonomy term entities.',
  ),
)]
class TaxonomyTermRepository extends TypedRepositoryBase {}
