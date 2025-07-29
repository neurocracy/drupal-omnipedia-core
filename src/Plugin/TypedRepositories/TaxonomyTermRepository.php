<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Plugin\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for wrapped taxonomy term entities.
 *
 * @TypedRepository(
 *   entity_type_id = "taxonomy_term",
 *   wrappers       = @ClassWithVariants(
 *     fallback = "Drupal\omnipedia_core\WrappedEntities\TaxonomyTerm",
 *     variants = {
 *       "Drupal\omnipedia_core\WrappedEntities\TaxonomyTermEpisode",
 *     }
 *   ),
 *   description = @Translation("The repository for taxonomy term entities.")
 * )
 */
class TaxonomyTermRepository extends TypedRepositoryBase {}
