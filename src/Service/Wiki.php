<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Service\WikiInterface;

/**
 * The Omnipedia wiki service.
 */
class Wiki implements WikiInterface {

  /**
   * The Drupal entity type plug-in manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructs this service object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager
  ) {
    // Save dependencies.
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Ensure a provided parameter is a node, loading it if need be.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Either a node object, or null if one cannot be loaded.
   */
  protected function normalizeNode($node) {
    if (\is_object($node) && $node instanceof NodeInterface) {
      return $node;

    } else if (\is_numeric($node)) {
      /** @var \Drupal\node\NodeInterface|null */
      return $this->entityTypeManager->getStorage('node')->load($node);

    } else {
      return null;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isWikiNode($node): bool {
    $node = $this->normalizeNode($node);

    if (\is_object($node) && $node instanceof NodeInterface) {
      return $node->getType() === 'wiki_page';

    } else {
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNode($node) {
    $node = $this->normalizeNode($node);

    if ($this->isWikiNode($node)) {
      return $node;
    } else {
      return null;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeDate($node) {
    $node = $this->getWikiNode($node);

    if ($node === null) {
      return null;
    }

    return $node->get('field_date')[0]->value;
  }

}
