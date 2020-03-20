<?php

namespace Drupal\omnipedia_core\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to update current date if a wiki page node is being viewed.
 *
 * @see \Symfony\Component\HttpKernel\KernelEvents::REQUEST
 *   Subscribes to this event to update the current date as early as possible
 *   if the current route contains a wiki page node in its parameters. The
 *   documentation states that this event is dispatched before "any other code
 *   in the framework is executed", but if this is still not early enough, there
 *   are other events in that class that we could subscribe to if need be.
 */
class TimelineWikiPageNodeEventSubscriber implements EventSubscriberInterface {
  /**
   * The Omnipedia timeline service.
   *
   * @var \Drupal\omnipedia_core\Service\TimelineInterface
   */
  private $timeline;

  /**
   * The Drupal current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The Drupal state system manager.
   */
  public function __construct(
    TimelineInterface   $timeline,
    RouteMatchInterface $routeMatch
  ) {
    $this->timeline   = $timeline;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => 'kernelRequest',
    ];
  }

  /**
   * Update the current date if a wiki page node is found in the route params.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Symfony response event object.
   */
  public function kernelRequest(GetResponseEvent $event): void {
    /** @var \Drupal\node\NodeInterface|NULL */
    $node = $this->routeMatch->getParameter('node');

    // Bail if no node was found or if the node is not a wiki page.
    if (
      gettype($node)    !== 'object' ||
      $node->getType()  !== 'wiki_page'
    ) {
      return;
    }

    $this->timeline->setCurrentDate($node->get('field_date')[0]->value);
  }
}
