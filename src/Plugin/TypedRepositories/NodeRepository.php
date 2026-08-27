<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Plugin\TypedRepositories;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\omnipedia_core\WrappedEntities\Node;
use Drupal\omnipedia_core\WrappedEntities\WikiNode;
use Drupal\typed_entity\Attribute\TypedRepository;
use Drupal\typed_entity\ClassWithVariants;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for wrapped node entities.
 */
#[TypedRepository(
  entity_type_id: 'node',
  wrappers: new ClassWithVariants(
    fallback: Node::class,
    variants: [
      WikiNode::class,
    ],
  ),
  description: new TranslatableMarkup('The repository for node entities.'),
)]
class NodeRepository extends TypedRepositoryBase {}
