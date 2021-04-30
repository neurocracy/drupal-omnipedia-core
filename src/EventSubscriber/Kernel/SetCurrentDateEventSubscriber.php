<?php

namespace Drupal\omnipedia_core\EventSubscriber\Kernel;

use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeRouteInterface;
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
class SetCurrentDateEventSubscriber implements EventSubscriberInterface {

  /**
   * The Drupal current route match service.
   *
   * @var \Drupal\Core\Routing\StackedRouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The Omnipedia timeline service.
   *
   * @var \Drupal\omnipedia_core\Service\TimelineInterface
   */
  protected $timeline;

  /**
   * The Omnipedia wiki node resolver service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeResolverInterface
   */
  protected $wikiNodeResolver;

  /**
   * The Omnipedia wiki node route service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeRouteInterface
   */
  protected $wikiNodeRoute;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_core\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRouteInterface $wikiNodeRoute
   *   The Omnipedia wiki node route service.
   */
  public function __construct(
    StackedRouteMatchInterface  $currentRouteMatch,
    TimelineInterface           $timeline,
    WikiNodeResolverInterface   $wikiNodeResolver,
    WikiNodeRouteInterface      $wikiNodeRoute
  ) {
    $this->currentRouteMatch  = $currentRouteMatch;
    $this->timeline           = $timeline;
    $this->wikiNodeResolver   = $wikiNodeResolver;
    $this->wikiNodeRoute      = $wikiNodeRoute;
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

    if (!$this->wikiNodeResolver->isWikiNode($node)) {
      return;
    }

    /** @var string|null */
    $currentDate = $node->getWikiNodeDate();

    // Bail if the date couldn't be found.
    if ($currentDate === null) {
      return;
    }

    $this->timeline->setCurrentDate($currentDate);
  }

}
