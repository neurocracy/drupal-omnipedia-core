<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;

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
   * The Omnipedia wiki node resolver service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeResolverInterface
   */
  protected $wikiNodeResolver;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   */
  public function __construct(
    StackedRouteMatchInterface  $currentRouteMatch,
    WikiNodeResolverInterface   $wikiNodeResolver
  ) {
    $this->currentRouteMatch  = $currentRouteMatch;
    $this->wikiNodeResolver   = $wikiNodeResolver;
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
    // If there's a 'node' route parameter, attempt to resolve it to a wiki
    // node. Note that the 'node' parameter is not upcast into a Node object if
    // viewing a (Drupal) revision other than the currently published one.
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveNode(
      $this->currentRouteMatch->getParameter('node')
    );

    // If this isn't a wiki node, return that as the context.
    if (!$this->wikiNodeResolver->isWikiNode($node)) {
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
