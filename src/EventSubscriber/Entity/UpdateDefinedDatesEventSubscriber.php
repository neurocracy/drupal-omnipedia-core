<?php

namespace Drupal\omnipedia_core\EventSubscriber\Entity;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Drupal\omnipedia_core\Service\WikiInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hook_event_dispatcher\Event\Entity\BaseEntityEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityDeleteEvent;

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
   * The Omnipedia wiki service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiInterface
   */
  protected $wiki;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiInterface $wiki
   *   The Omnipedia wiki service.
   */
  public function __construct(
    TimelineInterface $timeline,
    WikiInterface     $wiki
  ) {
    $this->timeline = $timeline;
    $this->wiki     = $wiki;
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
   * @param \Drupal\hook_event_dispatcher\Event\Entity\BaseEntityEvent $event
   *   The event object.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::isWikiNode()
   *   This method is used to determine if the entity is a wiki node.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::trackWikiNode()
   *   Calls this method to start tracking or update tracking of a wiki node.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::untrackWikiNode()
   *   Calls this method to stop tracking a wiki node.
   *
   * @see \Drupal\omnipedia_core\Service\TimelineInterface::findDefinedDates()
   *   Calls this method to invoke a rescan of wiki nodes.
   */
  public function updateDefinedDates(BaseEntityEvent $event) {
    /** @var \Drupal\Core\Entity\EntityInterface */
    $entity = $event->getEntity();

    if (!$this->wiki->isWikiNode($entity)) {
      return;
    }

    // If a node was created or updated, update tracking.
    if (
      $event instanceof EntityInsertEvent ||
      $event instanceof EntityUpdateEvent
    ) {
      $this->wiki->trackWikiNode($entity);

    // If a node was deleted, stop tracking it.
    } else if ($event instanceof EntityDeleteEvent) {
      $this->wiki->untrackWikiNode($entity);
    }

    // Rescan content to build list of defined dates.
    $this->timeline->findDefinedDates();
  }

}
