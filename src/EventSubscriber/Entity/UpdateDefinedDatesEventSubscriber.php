<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\EventSubscriber\Entity;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;

/**
 * Updates defined dates on wiki node entity changes.
 */
class UpdateDefinedDatesEventSubscriber implements EventSubscriberInterface {

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
   * The Omnipedia wiki node tracker service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected $wikiNodeTracker;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   */
  public function __construct(
    TimelineInterface         $timeline,
    WikiNodeResolverInterface $wikiNodeResolver,
    WikiNodeTrackerInterface  $wikiNodeTracker
  ) {
    $this->timeline         = $timeline;
    $this->wikiNodeResolver = $wikiNodeResolver;
    $this->wikiNodeTracker  = $wikiNodeTracker;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'updateDefinedDates',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'updateDefinedDates',
      HookEventDispatcherInterface::ENTITY_DELETE => 'updateDefinedDates',
    ];
  }

  /**
   * Update stored defined dates when a wiki node is created/updated/deleted.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent $event
   *   The event object.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeResolverInterface::isWikiNode()
   *   This method is used to determine if the entity is a wiki node.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::trackWikiNode()
   *   Calls this method to start tracking or update tracking of a wiki node.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::untrackWikiNode()
   *   Calls this method to stop tracking a wiki node.
   *
   * @see \Drupal\omnipedia_core\Service\TimelineInterface::findDefinedDates()
   *   Calls this method to invoke a rescan of wiki nodes.
   */
  public function updateDefinedDates(AbstractEntityEvent $event) {
    /** @var \Drupal\Core\Entity\EntityInterface */
    $entity = $event->getEntity();

    if (!$this->wikiNodeResolver->isWikiNode($entity)) {
      return;
    }

    // If a node was created or updated, update tracking.
    if (
      $event instanceof EntityInsertEvent ||
      $event instanceof EntityUpdateEvent
    ) {
      $this->wikiNodeTracker->trackWikiNode($entity, $entity->getWikiNodeDate());

    // If a node was deleted, stop tracking it.
    } else if ($event instanceof EntityDeleteEvent) {
      $this->wikiNodeTracker->untrackWikiNode($entity);
    }

    // Rescan content to build list of defined dates.
    $this->timeline->findDefinedDates();
  }

}
