<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\omnipedia_core\Entity\Node as WikiNode;
use Drupal\omnipedia_core\Service\WikiInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;

/**
 * The Omnipedia wiki service.
 */
class Wiki implements WikiInterface {

  /**
   * The Omnipedia wiki node resolver service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeResolverInterface
   */
  protected $wikiNodeResolver;

  /**
   * Constructs this service object.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   */
  public function __construct(
    WikiNodeResolverInterface $wikiNodeResolver
  ) {
    // Save dependencies.
    $this->wikiNodeResolver = $wikiNodeResolver;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeType(): string {
    return WikiNode::getWikiNodeType();
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeDateFieldName(): string {
    return WikiNode::getWikiNodeDateFieldName();
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeDate($node): ?string {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveNode($node);

    if ($this->wikiNodeResolver->isWikiNode($node)) {
      return $node->getWikiNodeDate();
    } else {
      return null;
    }
  }

}
