<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\omnipedia_core\Entity\NodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\Service\WikiNodeRouteInterface;

/**
 * The Omnipedia wiki node main page service.
 */
class WikiNodeMainPage implements WikiNodeMainPageInterface {

  /**
   * The Drupal state key where we store the node ID of the default main page.
   */
  protected const DEFAULT_MAIN_PAGE_STATE_KEY = 'omnipedia.default_main_page';

  /**
   * The Drupal cache ID where we store their computed cache IDs. (So meta.)
   */
  protected const MAIN_PAGES_CACHE_TAGS_ID = 'omnipedia.main_pages_tags';

  /**
   * The default Drupal cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected CacheBackendInterface $cache;

  /**
   * The Drupal configuration object factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The Drupal current route match service.
   *
   * @var \Drupal\Core\Routing\StackedRouteMatchInterface
   */
  protected StackedRouteMatchInterface $currentRouteMatch;

  /**
   * The Drupal state system manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $stateManager;

  /**
   * The Omnipedia wiki node resolver service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeResolverInterface
   */
  protected WikiNodeResolverInterface $wikiNodeResolver;

  /**
   * The Omnipedia wiki node revision service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface
   */
  protected WikiNodeRevisionInterface $wikiNodeRevision;

  /**
   * The Omnipedia wiki node route service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeRouteInterface
   */
  protected WikiNodeRouteInterface $wikiNodeRoute;

  /**
   * Constructs this service object.
   *
   * @param Drupal\Core\Cache\CacheBackendInterface $cache
   *   The default Drupal cache bin.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface $wikiNodeRevision
   *   The Omnipedia wiki node revision service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRouteInterface $wikiNodeRoute
   *   The Omnipedia wiki node route service.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    CacheBackendInterface       $cache,
    ConfigFactoryInterface      $configFactory,
    StackedRouteMatchInterface  $currentRouteMatch,
    WikiNodeResolverInterface   $wikiNodeResolver,
    WikiNodeRevisionInterface   $wikiNodeRevision,
    WikiNodeRouteInterface      $wikiNodeRoute,
    StateInterface              $stateManager
  ) {
    // Save dependencies.
    $this->cache              = $cache;
    $this->configFactory      = $configFactory;
    $this->currentRouteMatch  = $currentRouteMatch;
    $this->wikiNodeResolver   = $wikiNodeResolver;
    $this->wikiNodeRevision   = $wikiNodeRevision;
    $this->wikiNodeRoute      = $wikiNodeRoute;
    $this->stateManager       = $stateManager;
  }

  /**
   * {@inheritdoc}
   */
  public function isMainPage(mixed $node): bool {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveWikiNode($node);

    // Return false if this is not a wiki node.
    if (\is_null($node)) {
      return false;
    }

    /** @var array */
    $mainPageNids = $this->wikiNodeResolver
      ->nodeOrTitleToNids($this->getDefaultMainPage());

    return \in_array($node->nid->getString(), $mainPageNids);
  }

  /**
   * {@inheritdoc}
   */
  public function isCurrentRouteMainPage(): bool {

    // Return false if this route is not considered viewing a wiki node.
    if (!$this->wikiNodeRoute->isWikiNodeViewRouteName(
      $this->currentRouteMatch->getRouteName()
    )) {
      return false;
    }

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->currentRouteMatch->getParameter('node');

    return $this->isMainPage($node);

  }

  /**
   * Get the default main page node as configured in the site configuration.
   *
   * @return \Drupal\omnipedia_core\Entity\NodeInterface
   *
   * @throws \UnexpectedValueException
   *   Exception thrown when the configured front page is not a wiki node, if
   *   Url::fromUserInput() returns a non-routed URL, or if a date cannot be
   *   retrieved from the front page node.
   */
  protected function getDefaultMainPage(): NodeInterface {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveNode(
      $this->stateManager->get(self::DEFAULT_MAIN_PAGE_STATE_KEY)
    );

    if (\is_null($node)) {
      /** @var \Drupal\Core\Url */
      $urlObject = Url::fromUserInput(
        $this->configFactory->get('system.site')->get('page.front')
      );

      if (!$urlObject->isRouted()) {
        throw new \UnexpectedValueException(
          'The front page does not appear to point to an internal, routed URL.'
        );
      }

      /** @var array */
      $routeParameters = $urlObject->getRouteParameters();

      if (empty($routeParameters['node'])) {
        throw new \UnexpectedValueException(
          'The front page does not appear to point to a node.'
        );
      }

      /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
      $node = $this->wikiNodeResolver->resolveNode($routeParameters['node']);

      if (\is_null($node)) {
        throw new \UnexpectedValueException(
          'Could not load a valid node from the front page configuration.'
        );
      }
    }

    // Save to state storage.
    $this->stateManager->set(
      self::DEFAULT_MAIN_PAGE_STATE_KEY,
      $node->nid->getString()
    );

    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function updateDefaultMainPage(): void {
    // This just deletes the existing state data, so that it's recreated next
    // time the default main page is fetched.
    $this->stateManager->delete(self::DEFAULT_MAIN_PAGE_STATE_KEY);
  }

  /**
   * {@inheritdoc}
   *
   * @see $this->getDefaultMainPage()
   *   Loads the default main page as configured in the site configuration, so
   *   that we can retrieve its title - this avoids having to hard-code the
   *   title or any other information about it.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface::getWikiNodeRevision()
   *   Loads the indicated revision if the $date parameter is not 'default'.
   */
  public function getMainPage(string $date): ?NodeInterface {
    try {
      /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
      $default = $this->getDefaultMainPage();

    } catch (\Exception $exception) {
      return null;
    }

    if ($date === 'default') {
      return $default;
    }

    return $this->wikiNodeRevision->getWikiNodeRevision($default, $date);
  }

  /**
   * {@inheritdoc}
   */
  public function getMainPageRouteName(): string {
    return 'entity.node.canonical';
  }

  /**
   * {@inheritdoc}
   */
  public function getMainPageRouteParameters(string $date): array {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->getMainPage($date);

    // Fall back to the default main page if this date doesn't have one, to
    // avoid Drupal throwing an exception if we were to return an empty array.
    if (!($node instanceof NodeInterface)) {
      $node = $this->getDefaultMainPage();
    }

    return ['node' => $node->nid->getString()];
  }

  /**
   * {@inheritdoc}
   *
   * @todo Add a tag that gets invalidated when a main page is added or deleted,
   *   as this doesn't currently account for the former.
   */
  public function getMainPagesCacheTags(): array {
    /** @var object|false */
    $cache = $this->cache->get(self::MAIN_PAGES_CACHE_TAGS_ID);

    // If the computed tags are available in the cache, return those.
    if ($cache !== false) {
      return $cache->data;
    }

    /** @var array */
    $nids = $this->wikiNodeResolver->nodeOrTitleToNids(
      /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
      $this->getDefaultMainPage()
    );

    // Initial tags array containing the front page config tag.
    /** @var array */
    $tags = ['config:system.site.page.front'];

    foreach ($nids as $nid) {
      /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
      $node = $this->wikiNodeResolver->resolveNode($nid);

      if ($node === null) {
        continue;
      }

      /** @var array */
      $tags = Cache::mergeTags($tags, $node->getCacheTags());
    }

    // Save the computed tags into the cache. We also use the tags as their own
    // cache tags, which is super meta.
    $this->cache->set(
      self::MAIN_PAGES_CACHE_TAGS_ID,
      $tags,
      CacheBackendInterface::CACHE_PERMANENT,
      $tags
    );

    return $tags;
  }

}
