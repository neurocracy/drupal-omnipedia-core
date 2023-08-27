<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\omnipedia_core\Entity\NodeInterface;
use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;

/**
 * The Omnipedia wiki node resolver service.
 */
class WikiNodeResolver implements WikiNodeResolverInterface {

  /**
   * Constructs this service object; saves dependencies.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly WikiNodeTrackerInterface   $wikiNodeTracker,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function resolveNode(mixed $node): ?NodeInterface {
    if (\is_object($node) && $node instanceof NodeInterface) {
      return $node;

    } else if (\is_numeric($node)) {
      /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
      return $this->entityTypeManager->getStorage('node')->load($node);

    } else {
      return null;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function nodeOrTitleToNids(
    NodeInterface|int|string $nodeOrTitle
  ): array {
    if (\is_string($nodeOrTitle)) {
      /** @var string */
      $title = $nodeOrTitle;

    } else {
      /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
      $node = $this->resolveNode($nodeOrTitle);

      if ($node instanceof NodeInterface) {
        /** @var string */
        $title = $node->getTitle();
      }
    }

    if (!isset($title)) {
      throw new \InvalidArgumentException('The $nodeOrTitle parameter must be a node object, an integer node ID (nid), or a node title as a string.');
    }

    /** @var array */
    $nodeData = $this->wikiNodeTracker->getTrackedWikiNodeData();

    // Get all node IDs of nodes with this title.
    return \array_keys($nodeData['titles'], $title, true);
  }

  /**
   * {@inheritdoc}
   */
  public function isWikiNode(mixed $node): bool {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->resolveNode($node);

    if (\is_object($node) && $node instanceof NodeInterface) {
      return $node->getType() === WikiNodeInfo::TYPE;
    } else {
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function resolveWikiNode(mixed $node): ?NodeInterface {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->resolveNode($node);

    if ($this->isWikiNode($node)) {
      return $node;
    } else {
      return null;
    }
  }

}
