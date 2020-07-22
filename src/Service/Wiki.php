<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\Node as WikiNode;
use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;
use Drupal\omnipedia_core\Service\WikiInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The Omnipedia wiki service.
 */
class Wiki implements WikiInterface {

  /**
   * The Drupal state key where we store the node ID of the default main page.
   */
  protected const DEFAULT_MAIN_PAGE_STATE_KEY = 'omnipedia.default_main_page';

  /**
   * The Symfony session attribute key where we store the recently viewed nodes.
   *
   * @see https://symfony.com/doc/3.4/components/http_foundation/sessions.html#namespaced-attributes
   */
  protected const RECENT_WIKI_NODES_SESSION_KEY = 'omnipedia/recentWikiNodes';

  /**
   * The number of recent wiki nodes to track for a user.
   *
   * @todo Should this be stored in config and given an admin form?
   */
  protected const RECENT_WIKI_NODES_COUNT = 5;

  /**
   * The Drupal configuration object factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Symfony session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * The Drupal state system manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $stateManager;

  /**
   * The Omnipedia wiki node resolver service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeResolverInterface
   */
  protected $wikiNodeResolver;

  /**
   * The Omnipedia wiki node revision service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface
   */
  protected $wikiNodeRevision;

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected $wikiNodeTracker;

  /**
   * Constructs this service object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface $wikiNodeRevision
   *   The Omnipedia wiki node revision service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The Symfony session service.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    ConfigFactoryInterface      $configFactory,
    WikiNodeResolverInterface   $wikiNodeResolver,
    WikiNodeRevisionInterface   $wikiNodeRevision,
    WikiNodeTrackerInterface    $wikiNodeTracker,
    SessionInterface            $session,
    StateInterface              $stateManager
  ) {
    // Save dependencies.
    $this->configFactory      = $configFactory;
    $this->wikiNodeResolver   = $wikiNodeResolver;
    $this->wikiNodeRevision   = $wikiNodeRevision;
    $this->wikiNodeTracker    = $wikiNodeTracker;
    $this->session            = $session;
    $this->stateManager       = $stateManager;
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

  /**
   * {@inheritdoc}
   */
  public function getTrackedWikiNodeData(): array {
    return $this->wikiNodeTracker->getTrackedWikiNodeData();
  }

  /**
   * {@inheritdoc}
   */
  public function trackWikiNode($node): void {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->getWikiNode($node);

    // Bail if no node could be loaded.
    if ($node === null) {
      return;
    }

    /** @var string */
    $nodeDate = $node->getWikiNodeDate();

    $this->wikiNodeTracker->trackWikiNode($node, $nodeDate);
  }

  /**
   * {@inheritdoc}
   */
  public function untrackWikiNode($node): void {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->getWikiNode($node);

    // Bail if no node could be loaded.
    if ($node === null) {
      return;
    }

    $this->wikiNodeTracker->untrackWikiNode($node);
  }

  /**
   * Get the default main page node as configured in the site configuration.
   *
   * @return \Drupal\omnipedia_core\Entity\NodeInterface
   *
   * @throws \UnexpectedValueException
   *   Exception thrown when the configured front page is not a node or a date
   *   cannot be retrieved from the front page node.
   */
  protected function getDefaultMainPage(): WikiNodeInterface {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveNode(
      $this->stateManager->get(self::DEFAULT_MAIN_PAGE_STATE_KEY)
    );

    if (\is_null($node)) {
      /** @var \Drupal\Core\Url */
      $urlObject = Url::fromUserInput(
        $this->configFactory->get('system.site')->get('page.front')
      );

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
  public function isMainPage($node): bool {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->getWikiNode($node);

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
  public function getMainPage(string $date): ?WikiNodeInterface {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
    $default = $this->getDefaultMainPage();

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
   */
  public function addRecentlyViewedWikiNode($node): void {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->getWikiNode($node);

    // Return if this is not a wiki node.
    if (\is_null($node)) {
      return;
    }

    /** @var string */
    $nid = $node->nid->getString();

    /** @var array */
    $viewedNids = $this->getRecentlyViewedWikiNodes();

    /** @var array */
    $mainPageNids = $this->wikiNodeResolver
      ->nodeOrTitleToNids($this->getDefaultMainPage());

    // Bail if the nid is already in the viewed array so that we don't record it
    // twice. This is to guard against erroneously calling this more than once
    // during a redirect or similar situation. Additionally, do not record main
    // page nids.
    if (\in_array($nid, $viewedNids) || \in_array($nid, $mainPageNids)) {
      return;
    }

    $viewedNids[] = $nid;

    // Remove any viewed nids from the end of the array that exceed the recent
    // wiki nodes count limit. Note that array_slice() correctly handles array
    // lengths lower than or equal to the provided length parameter by returning
    // the array as-is with no changes.
    $viewedNids = \array_reverse(\array_slice(
      \array_reverse($viewedNids), 0, self::RECENT_WIKI_NODES_COUNT
    ));

    // Save to session storage.
    $this->session->set(self::RECENT_WIKI_NODES_SESSION_KEY, $viewedNids);
  }

  /**
   * Get the most recent wiki nodes viewed by the current user, if any.
   *
   * @return array
   *   An array of nids, or an empty array if no recent wiki nodes were found in
   *   the user's session.
   *
   * @see self::RECENT_WIKI_NODES_SESSION_KEY
   *   Session key where array is stored.
   *
   * @see $this->addRecentlyViewedWikiNode()
   *   Nodes are added to the user's session via this method.
   */
  protected function getRecentlyViewedWikiNodes(): array {
    if ($this->session->has(self::RECENT_WIKI_NODES_SESSION_KEY)) {
      return $this->session->get(self::RECENT_WIKI_NODES_SESSION_KEY);
    } else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRandomWikiNodeRouteParameters(string $date): array {
    /** @var array */
    $nodeData = $this->getTrackedWikiNodeData();

    /** @var array */
    $mainPageNids = $this->wikiNodeResolver
      ->nodeOrTitleToNids($this->getDefaultMainPage());

    /** @var array */
    $viewedNids = $this->getRecentlyViewedWikiNodes();

    /** @var array */
    $nids = \array_filter(
      $nodeData['dates'][$date],
      function($nid) use ($nodeData, $mainPageNids, $viewedNids) {
        // This filters out unpublished nodes, main page nodes, and recently
        // viewed wiki nodes.
        return !(
          !$nodeData['nodes'][$nid]['published'] ||
          \in_array($nid, $mainPageNids) ||
          \in_array($nid, $viewedNids)
        );
      }
    );

    return [
      // Return a random nid from the available nids.
      'node' => $nids[\array_rand($nids)]
    ];
  }

}
