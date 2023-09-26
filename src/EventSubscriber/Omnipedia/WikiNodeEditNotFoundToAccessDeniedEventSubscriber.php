<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\EventSubscriber\Omnipedia;

use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\omnipedia_access\Event\Omnipedia\AccessDeniedToNotFoundEvent;
use Drupal\omnipedia_access\Event\Omnipedia\AccessDeniedToNotFoundEventsInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Event subscriber to allow showing a 403 instead of a 404 for wiki node edit.
 */
class WikiNodeEditNotFoundToAccessDeniedEventSubscriber implements EventSubscriberInterface {

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The Drupal current user account proxy service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   */
  public function __construct(
    protected readonly StackedRouteMatchInterface $currentRouteMatch,
    protected readonly AccountProxyInterface      $currentUser,
    protected readonly WikiNodeResolverInterface  $wikiNodeResolver,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      AccessDeniedToNotFoundEventsInterface::ACCESS_DENIED_TO_NOT_FOUND => 'onAccessDeniedToNotFound',
    ];
  }

  /**
   * Access denied to not found event handler.
   *
   * @param \Drupal\omnipedia_access\Event\Omnipedia\AccessDeniedToNotFoundEvent $event
   *   The event object.
   */
  public function onAccessDeniedToNotFound(
    AccessDeniedToNotFoundEvent $event,
  ): void {

    if ($this->currentRouteMatch->getRouteName() !== 'entity.node.edit_form') {
      return;
    }

    // If there's a 'node' route parameter, attempt to resolve it to a wiki
    // node. Note that the 'node' parameter is not upcast into a Node object if
    // viewing a (Drupal) revision other than the currently published one.
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveWikiNode(
      $this->currentRouteMatch->getParameter('node'),
    );

    if ($node === null) {
      return;
    }

    // Only show the 403 if the current user has view access to the node in
    // question.
    //
    // @see \Drupal\omnipedia_core\EventSubscriber\Menu\WikiNodeEditLocalTaskEventSubscriber::onMenuLocalTaskAlter()
    //   Must be kept in sync with this with the same logic.
    if (
      $node->access('view', $this->currentUser) === false
    ) {
      return;
    }

    $event->setThrowable(new AccessDeniedHttpException());

  }

}
