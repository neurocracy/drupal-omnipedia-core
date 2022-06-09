<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\EventSubscriber\Kernel;

use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeRouteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to record when a wiki node is viewed.
 *
 * @see \Symfony\Component\HttpKernel\KernelEvents::RESPONSE
 *   Subscribes to this event to record the last wiki node viewed, if
 *   applicable.
 */
class WikiNodeViewedEventSubscriber implements EventSubscriberInterface {

  /**
   * The Drupal current route match service.
   *
   * @var \Drupal\Core\Routing\StackedRouteMatchInterface
   */
  protected StackedRouteMatchInterface $currentRouteMatch;

  /**
   * The Omnipedia wiki node resolver service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeResolverInterface
   */
  protected WikiNodeResolverInterface $wikiNodeResolver;

  /**
   * The Omnipedia wiki node route service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeRouteInterface
   */
  protected WikiNodeRouteInterface $wikiNodeRoute;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRouteInterface $wikiNodeRoute
   *   The Omnipedia wiki node route service.
   */
  public function __construct(
    StackedRouteMatchInterface  $currentRouteMatch,
    WikiNodeResolverInterface   $wikiNodeResolver,
    WikiNodeRouteInterface      $wikiNodeRoute
  ) {
    $this->currentRouteMatch  = $currentRouteMatch;
    $this->wikiNodeResolver   = $wikiNodeResolver;
    $this->wikiNodeRoute      = $wikiNodeRoute;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => 'kernelResponse',
    ];
  }

  /**
   * Record the last wiki node viewed.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   Symfony filter response event object.
   */
  public function kernelResponse(FilterResponseEvent $event): void {
    // Bail if this is not a node page to avoid false positives.
    if (!$this->wikiNodeRoute->isWikiNodeViewRouteName(
      $this->currentRouteMatch->getRouteName()
    )) {
      return;
    }

    // If there's a 'node' route parameter, attempt to resolve it to a wiki
    // node. Note that the 'node' parameter is not upcast into a Node object if
    // viewing a (Drupal) revision other than the currently published one.
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveNode(
      $this->currentRouteMatch->getParameter('node')
    );

    if ($node === null) {
      return;
    }

    $node->addRecentlyViewedWikiNode();
  }

}
