<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\WrappedEntities\Node;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity\TypedEntityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wraps the wiki node entity.
 */
class WikiNode extends Node {

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\typed_entity\RepositoryManager $repositoryManager
   *   The Typed Entity repository manager.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface $wikiNodeRevision
   *   The Omnipedia wiki node revision service.
   */
  public function __construct(
    EntityInterface $entity,
    protected ?RepositoryManager $repositoryManager,
    protected readonly WikiNodeRevisionInterface $wikiNodeRevision,
  ) {

    parent::__construct($entity);

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
      $container->get('omnipedia.wiki_node_revision'),
    );
  }

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

  /**
   * {@inheritdoc}
   */
  public function getWikiRevisions(): array {
    return $this->wikiNodeRevision->getWikiNodeRevisions($this->getEntity());
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiRevision(string $date): ?NodeWithWikiInfoInterface {

    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->wikiNodeRevision->getWikiNodeRevision(
      $this->getEntity(), $date,
    );

    if (\is_object($node)) {

      return $this->repositoryManager->wrap($node);

    }

    return null;

  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousWikiRevision(): ?NodeWithWikiInfoInterface {

    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->wikiNodeRevision->getPreviousRevision($this->getEntity());

    if (\is_object($node)) {

      return $this->repositoryManager->wrap($node);

    }

    return null;

  }

  /**
   * {@inheritdoc}
   */
  public function hasPreviousWikiRevision(): bool {

    return $this->wikiNodeRevision->hasPreviousRevision($this->getEntity());

  }

}
