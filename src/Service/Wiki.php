<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Service\WikiInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The Omnipedia wiki service.
 */
class Wiki implements WikiInterface {

  /**
   * The wiki node type.
   */
  protected const WIKI_NODE_TYPE = 'wiki_page';

  /**
   * The name of the date field on wiki nodes.
   */
  protected const WIKI_NODE_DATE_FIELD = 'field_date';

  /**
   * The Drupal state key where we store the node ID of the default main page.
   *
   * @see \Drupal\omnipedia_core\EventSubscriber\Config\SystemSiteFrontPageConfigEventSubscriber::configSave()
   *   Constant is public so that this event subscriber can access it.
   */
  public const DEFAULT_MAIN_PAGE_STATE_KEY = 'omnipedia.default_main_page';

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
   * The Drupal entity type plug-in manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
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
    EntityTypeManagerInterface  $entityTypeManager,
    WikiNodeTrackerInterface    $wikiNodeTracker,
    SessionInterface            $session,
    StateInterface              $stateManager
  ) {
    // Save dependencies.
    $this->configFactory      = $configFactory;
    $this->entityTypeManager  = $entityTypeManager;
    $this->wikiNodeTracker    = $wikiNodeTracker;
    $this->session            = $session;
    $this->stateManager       = $stateManager;
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
  public function getWikiNodeType(): string {
    return self::WIKI_NODE_TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function isWikiNode($node): bool {
    $node = $this->normalizeNode($node);

    if (\is_object($node) && $node instanceof NodeInterface) {
      return $node->getType() === $this->getWikiNodeType();

    } else {
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNode($node): ?NodeInterface {
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
  public function getWikiNodeDateFieldName(): string {
    return self::WIKI_NODE_DATE_FIELD;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeDate($node): ?string {
    $node = $this->getWikiNode($node);

    if ($node === null) {
      return null;
    }

    return $node->get($this->getWikiNodeDateFieldName())[0]->value;
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
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->getWikiNode($node);

    // Bail if no node could be loaded.
    if ($node === null) {
      return;
    }

    /** @var string */
    $nodeDate = $this->getWikiNodeDate($node);

    $this->wikiNodeTracker->trackWikiNode($node, $nodeDate);
  }

  /**
   * {@inheritdoc}
   */
  public function untrackWikiNode($node): void {
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->getWikiNode($node);

    // Bail if no node could be loaded.
    if ($node === null) {
      return;
    }

    $this->wikiNodeTracker->untrackWikiNode($node);
  }

  /**
   * Resolve a node or title to all node IDs with the same title.
   *
   * @param \Drupal\node\NodeInterface|int|string $nodeOrTitle
   *   Must be one of the following:
   *
   *   - An instance of \Drupal\node\NodeInterface, i.e. a node object
   *
   *   - An integer or a numeric string that equates to a node ID
   *
   *   - A non-numeric string which is assumed to be a node title to search for
   *
   * @return array
   *   An array containing zero or more node IDs as values.
   */
  protected function nodeOrTitleToNids($nodeOrTitle): array {
    if (\is_string($nodeOrTitle)) {
      /** @var string */
      $title = $nodeOrTitle;

    } else {
      /** @var \Drupal\node\NodeInterface|null */
      $node = $this->normalizeNode($nodeOrTitle);

      if ($node instanceof NodeInterface) {
        /** @var string */
        $title = $node->getTitle();
      }
    }

    if (!isset($title)) {
      throw new \InvalidArgumentException('The $nodeOrTitle parameter must be a node object, an integer node ID, or a node title as a string.');
    }

    /** @var array */
    $nodeData = $this->getTrackedWikiNodeData();

    // Get all node IDs of nodes with this title.
    return \array_keys($nodeData['titles'], $title, true);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Move date sorting to \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::trackWikiNode()
   *   so that it doesn't need to be done every time this method is called.
   */
  public function getWikiNodeRevisions($nodeOrTitle): array {
    /** @var array */
    $nodeData = $this->getTrackedWikiNodeData();

    /** @var array */
    $nodes = [];

    /** @var array */
    $nids = $this->nodeOrTitleToNids($nodeOrTitle);

    foreach ($nids as $nid) {
      // The node's date parsed into an array of date parts. We do this so that
      // we don't need to have any knowledge of what format the date is in, just
      // as long as it can be parsed.
      /** @var array */
      $dateArray = \date_parse($nodeData['nodes'][$nid]['date']);

      $nodes[$nid] = [
        'nid'       => $nid,
        // We need to build DrupalDateTime objects so that we can use their
        // diff() method to sort by date. We later change this back to the
        // formatted string.
        'date'      => DrupalDateTime::createFromArray([
          'year'      => $dateArray['year'],
          'month'     => $dateArray['month'],
          'day'       => $dateArray['day'],
        ]),
        'title'     => $nodeData['nodes'][$nid]['title'],
        'published' => $nodeData['nodes'][$nid]['published'],
      ];
    }

    // Sort the array by their dates.
    \usort($nodes, function($a, $b) {
      // DrupalDateTime::diff() returns a \DateInterval object, which contains a
      // 'days' property:
      //
      // @see https://www.php.net/manual/en/class.dateinterval.php
      return $a['date']->diff($b['date'])->days * -1;
    });

    return $nodes;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeRevision($nodeOrTitle, string $date): ?NodeInterface {
    // Get all node IDs of nodes with this title.
    /** @var array */
    $nids = $this->nodeOrTitleToNids($nodeOrTitle);

    /** @var array */
    $nodeData = $this->getTrackedWikiNodeData();

    // Loop through all found nodes and return the first one that has the date
    // we're looking for.
    foreach ($nids as $nid) {
      if ($nodeData['nodes'][$nid]['date'] !== $date) {
        continue;
      }

      return $this->normalizeNode($nid);
    }

    // No node with that date found.
    return null;
  }

  /**
   * Get the default main page node as configured in the site configuration.
   *
   * @return \Drupal\node\NodeInterface
   *
   * @throws \UnexpectedValueException
   *   Exception thrown when the configured front page is not a node or a date
   *   cannot be retrieved from the front page node.
   */
  protected function getDefaultMainPage(): NodeInterface {
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->normalizeNode(
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

      /** @var \Drupal\node\NodeInterface|null */
      $node = $this->normalizeNode($routeParameters['node']);

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
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->getWikiNode($node);

    // Return false if this is not a wiki node.
    if (\is_null($node)) {
      return false;
    }

    /** @var array */
    $mainPageNids = $this->nodeOrTitleToNids($this->getDefaultMainPage());

    return \in_array($node->nid->getString(), $mainPageNids);
  }

  /**
   * {@inheritdoc}
   *
   * @see $this->getDefaultMainPage()
   *   Loads the default main page as configured in the site configuration, so
   *   that we can retrieve its title - this avoids having to hard-code the
   *   title or any other information about it.
   *
   * @see $this->getWikiNodeRevision()
   *   Loads the indicated revision if the $date parameter is not 'default'.
   */
  public function getMainPage(string $date): ?NodeInterface {
    /** @var \Drupal\node\NodeInterface */
    $default = $this->getDefaultMainPage();

    if ($date === 'default') {
      return $default;
    }

    return $this->getWikiNodeRevision($default, $date);
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
    /** @var \Drupal\node\NodeInterface|null */
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
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->getWikiNode($node);

    // Return if this is not a wiki node.
    if (\is_null($node)) {
      return;
    }

    /** @var string */
    $nid = $node->nid->getString();

    /** @var array */
    $viewedNids = $this->getRecentlyViewedWikiNodes();

    /** @var array */
    $mainPageNids = $this->nodeOrTitleToNids($this->getDefaultMainPage());

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
    $mainPageNids = $this->nodeOrTitleToNids($this->getDefaultMainPage());

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
