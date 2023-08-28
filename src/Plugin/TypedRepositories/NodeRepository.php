<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Plugin\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for wrapped node entities.
 *
 * @TypedRepository(
 *   entity_type_id = "node",
 *   wrappers       = @ClassWithVariants(
 *     fallback = "Drupal\omnipedia_core\WrappedEntities\Node",
 *     variants = {
 *       "Drupal\omnipedia_core\WrappedEntities\WikiNode",
 *     }
 *   ),
 *   description = @Translation("The repository for node entities.")
 * )
 */
class NodeRepository extends TypedRepositoryBase {}
