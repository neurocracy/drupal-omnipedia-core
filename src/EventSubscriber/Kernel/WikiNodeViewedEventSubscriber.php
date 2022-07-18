<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\EventSubscriber\Kernel;

use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeRouteInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
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

  use StringTranslationTrait;

  /**
   * The Drupal current route match service.
   *
   * @var \Drupal\Core\Routing\StackedRouteMatchInterface
   */
  protected StackedRouteMatchInterface $currentRouteMatch;

  /**
   * Our logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $loggerChannel;

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
   * @param \Psr\Log\LoggerInterface $loggerChannel
   *   Our logger channel.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRouteInterface $wikiNodeRoute
   *   The Omnipedia wiki node route service.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The Drupal string translation service.
   */
  public function __construct(
    StackedRouteMatchInterface  $currentRouteMatch,
    LoggerInterface             $loggerChannel,
    WikiNodeResolverInterface   $wikiNodeResolver,
    WikiNodeRouteInterface      $wikiNodeRoute,
    TranslationInterface        $stringTranslation
  ) {
    $this->currentRouteMatch  = $currentRouteMatch;
    $this->loggerChannel      = $loggerChannel;
    $this->stringTranslation  = $stringTranslation;
    $this->wikiNodeResolver   = $wikiNodeResolver;
    $this->wikiNodeRoute      = $wikiNodeRoute;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST   => ['onKernelRequest', 1000],
      KernelEvents::RESPONSE  => 'onKernelResponse',
    ];
  }

  public function onKernelRequest(RequestEvent $event): void {

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

      $this->loggerChannel->debug(
        'WikiNodeViewedEventSubscriber could not resolve node parameter:<pre>%node</pre>',
        [
          '%node'  => \print_r(
            $this->currentRouteMatch->getParameter('node'), true
          ),
        ]
      );

      return;

    }

    $node->addRecentlyViewedWikiNode();

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
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveNode(
      $this->currentRouteMatch->getParameter('node')
    );

    if ($node === null) {

      $this->loggerChannel->debug(
        'WikiNodeViewedEventSubscriber could not resolve node parameter:<pre>%node</pre>',
        [
          '%node'  => \print_r(
            $this->currentRouteMatch->getParameter('node'), true
          ),
        ]
      );

      return;

    }

    $node->addRecentlyViewedWikiNode();

  }

}
