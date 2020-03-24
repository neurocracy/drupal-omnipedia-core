<?php

namespace Drupal\omnipedia_core\EventSubscriber;

use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Drupal\omnipedia_core\Service\WikiInterface;
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
   * The Drupal current route match service.
   *
   * @var \Drupal\Core\Routing\StackedRouteMatchInterface
   */
  private $currentRouteMatch;

  /**
   * The Omnipedia timeline service.
   *
   * @var \Drupal\omnipedia_core\Service\TimelineInterface
   */
  private $timeline;

  /**
   * The Omnipedia wiki service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiInterface
   */
  private $wiki;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_core\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiInterface $wiki
   *   The Omnipedia wiki service.
   */
  public function __construct(
    StackedRouteMatchInterface  $currentRouteMatch,
    TimelineInterface           $timeline,
    WikiInterface               $wiki
  ) {
    $this->currentRouteMatch  = $currentRouteMatch;
    $this->timeline           = $timeline;
    $this->wiki               = $wiki;
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
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->currentRouteMatch->getParameter('node');

    /** @var string|null */
    $currentDate = $this->wiki->getWikiNodeDate($node);

    // Bail if the date couldn't be found.
    if ($currentDate === null) {
      return;
    }

    $this->timeline->setCurrentDate($currentDate);
  }

}
