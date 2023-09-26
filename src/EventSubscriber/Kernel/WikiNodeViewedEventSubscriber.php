<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\EventSubscriber\Kernel;

use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeRouteInterface;
use Drupal\omnipedia_core\Service\WikiNodeViewedInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRouteInterface $wikiNodeRoute
   *   The Omnipedia wiki node route service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeViewedInterface $wikiNodeViewed
   *   The Omnipedia wiki node viewed service.
   */
  public function __construct(
    protected readonly StackedRouteMatchInterface $currentRouteMatch,
    protected readonly WikiNodeResolverInterface  $wikiNodeResolver,
    protected readonly WikiNodeRouteInterface     $wikiNodeRoute,
    protected readonly WikiNodeViewedInterface    $wikiNodeViewed,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => 'onKernelResponse',
    ];
  }

  /**
   * Record the last wiki node viewed.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   Symfony filter response event object.
   */
  public function onKernelResponse(ResponseEvent $event): void {

    // Bail if this is not a node page to avoid false positives.
    if (!$this->wikiNodeRoute->isWikiNodeViewRouteName(
      $this->currentRouteMatch->getRouteName()
    )) {
      return;
    }

    // If there's a 'node' route parameter, attempt to resolve it to a wiki
    // node. Note that the 'node' parameter is not upcast into a Node object if
    // viewing a (Drupal) revision other than the currently published one.
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveNode(
      $this->currentRouteMatch->getParameter('node')
    );

    if ($node === null) {
      return;
    }

    $this->wikiNodeViewed->addNode($node);

  }

}
