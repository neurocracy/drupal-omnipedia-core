<?php

namespace Drupal\omnipedia_core\EventSubscriber\Kernel;

use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\WikiInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to record the last wiki node viewed.
 *
 * @see \Symfony\Component\HttpKernel\KernelEvents::RESPONSE
 *   Subscribes to this event to record the last wiki node viewed, if
 *   applicable.
 */
class RecentlyViewedWikiNodeEventSubscriber implements EventSubscriberInterface {

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
   * Event subscriber constructor; saves dependencies.
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
    if ($this->currentRouteMatch->getRouteName() !== 'entity.node.canonical') {
      return;
    }

    $this->wiki->addRecentlyViewedWikiNode(
      /** @var \Drupal\node\NodeInterface|null */
      $this->currentRouteMatch->getParameter('node')
    );
  }

}
