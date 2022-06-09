<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;

/**
 * The Omnipedia wiki node resolver service.
 */
class WikiNodeResolver implements WikiNodeResolverInterface {

  /**
   * The Drupal entity type plug-in manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected WikiNodeTrackerInterface $wikiNodeTracker;

  /**
   * Constructs this service object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   */
  public function __construct(
    EntityTypeManagerInterface  $entityTypeManager,
    WikiNodeTrackerInterface    $wikiNodeTracker
  ) {
    // Save dependencies.
    $this->entityTypeManager  = $entityTypeManager;
    $this->wikiNodeTracker    = $wikiNodeTracker;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveNode(mixed $node): ?WikiNodeInterface {
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
    WikiNodeInterface|int|string $nodeOrTitle
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
      return $node->isWikiNode();
    } else {
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNode(mixed $node): ?WikiNodeInterface {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->resolveNode($node);

    if ($this->isWikiNode($node)) {
      return $node;
    } else {
      return null;
    }
  }

}
