<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;

/**
 * The Omnipedia wiki node main page service.
 */
class WikiNodeMainPage implements WikiNodeMainPageInterface {

  /**
   * The Drupal state key where we store the node ID of the default main page.
   */
  protected const DEFAULT_MAIN_PAGE_STATE_KEY = 'omnipedia.default_main_page';

  /**
   * The Drupal configuration object factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    ConfigFactoryInterface    $configFactory,
    WikiNodeResolverInterface $wikiNodeResolver,
    WikiNodeRevisionInterface $wikiNodeRevision,
    StateInterface            $stateManager
  ) {
    // Save dependencies.
    $this->configFactory    = $configFactory;
    $this->wikiNodeResolver = $wikiNodeResolver;
    $this->wikiNodeRevision = $wikiNodeRevision;
    $this->stateManager     = $stateManager;
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

}
