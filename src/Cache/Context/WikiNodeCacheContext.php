<?php

namespace Drupal\omnipedia_core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\WikiInterface;

/**
 * Defines the Omnipedia wiki node cache context service.
 *
 * Cache context ID: 'omnipedia_wiki_node'.
 *
 * This allows for caching to vary per Omnipedia wiki node and when the current
 * route is not a wiki node.
 */
class WikiNodeCacheContext implements CalculatedCacheContextInterface {

  /**
   * The Drupal current route match service.
   *
   * @var \Drupal\Core\Routing\StackedRouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The Omnipedia wiki service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiInterface
   */
  protected $wiki;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiInterface $wiki
   *   The Omnipedia wiki service.
   */
  public function __construct(
    StackedRouteMatchInterface  $currentRouteMatch,
    WikiInterface               $wiki
  ) {
    $this->currentRouteMatch  = $currentRouteMatch;
    $this->wiki               = $wiki;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return \t('Omnipedia wiki node');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = null) {
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->currentRouteMatch->getParameter('node');

    // If this isn't a wiki node, return that as the context.
    if (!$this->wiki->isWikiNode($node)) {
      return 'not_wiki_node';
    }

    // If this is a wiki node, return its nid as the context.
    return $node->nid->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = null) {
    return new CacheableMetadata();
  }

}