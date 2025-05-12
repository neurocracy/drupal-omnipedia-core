<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core_content_moderation\WrappedEntities;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\WrappedEntities\WikiNode;
use Drupal\omnipedia_core_content_moderation\WrappedEntities\ModerationStateTrait;
use Drupal\typed_entity\EntityWrapperInterface;
use Drupal\typed_entity\TypedEntityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wraps the wiki node entity when it has moderation states.
 */
class WikiNodeWithModerationState extends WikiNode {

  use ModerationStateTrait;

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to wrap.
   *
   * @param \Drupal\typed_entity\EntityWrapperInterface $typedRepositoryManager
   *   The Typed Entity repository manager.
   *
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderationInfo
   *   The content moderation info service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface $wikiNodeRevision
   *   The Omnipedia wiki node revision service.
   */
  public function __construct(
    EntityInterface $entity,
    EntityWrapperInterface $typedRepositoryManager,
    protected readonly ModerationInformationInterface $moderationInfo,
    WikiNodeRevisionInterface $wikiNodeRevision,
  ) {

    parent::__construct($entity, $typedRepositoryManager, $wikiNodeRevision);

    // WrappedEntityBase doesn't inject this and if you call
    // $this->repositoryManager() without setting this, it'll get it using the
    // \Drupal static class. Since we're in a real dependency injection context,
    // we can just inject it ourselves from the container.
    //
    // @see \Drupal\typed_entity\WrappedEntities\WrappedEntityBase::repositoryManager()
    $this->setRepositoryManager($typedRepositoryManager);

  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container, EntityInterface $entity,
  ) {

    return new static(
      $entity,
      $container->get('Drupal\typed_entity\RepositoryManager'),
      $container->get('content_moderation.moderation_information'),
      $container->get('omnipedia.wiki_node_revision'),
    );

  }

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {

    return (
      $context->offsetGet('entity')->hasField('moderation_state') &&
      parent::applies($context)
    );

  }

}
