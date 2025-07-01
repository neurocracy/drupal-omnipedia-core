<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core_refreshless\EventSubscriber\Kernel;

use Drupal\omnipedia_core\EventSubscriber\Kernel\WikiNodeViewedEventSubscriber as DecoratedEventSubscriber;
use Drupal\refreshless\Service\RequestWrapperFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Prevent recording wiki node viewed if a RefreshLess prefetch/preload request.
 *
 * This decorates the parent module's event subscriber to only call its event
 * handler method if this is not a RefreshLess prefetch or preload request.
 */
class WikiNodeViewedEventSubscriber implements EventSubscriberInterface {

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\EventSubscriber\Kernel\WikiNodeViewedEventSubscriber $decorated
   *   The event subscriber that we decorate.
   *
   * @param \Drupal\refreshless\Service\RequestWrapperFactoryInterface $requestWrapperFactory
   *   The RefreshLess request wrapper factory.
   */
  public function __construct(
    #[AutowireDecorated]
    protected readonly DecoratedEventSubscriber $decorated,
    protected readonly RequestWrapperFactoryInterface $requestWrapperFactory,
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
   * Prevent recording last wiki node viewed if RefreshLess prefetch/preload.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   Symfony filter response event object.
   */
  public function onKernelResponse(ResponseEvent $event): void {

    $requestWrapper = $this->requestWrapperFactory->fromRequest(
      $event->getRequest(),
    );

    if ($requestWrapper->isRefreshless() === true && (
      $requestWrapper->isPrefetch() === true ||
      $requestWrapper->isPreload() === true
    )) {
      return;
    }

    $this->decorated->onKernelResponse($event);

  }

}
