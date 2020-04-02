<?php

namespace Drupal\omnipedia_core\EventSubscriber\Entity;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Drupal\omnipedia_core\Service\WikiInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hook_event_dispatcher\Event\Entity\BaseEntityEvent;

/**
 * Updates defined dates on wiki node entity changes.
 */
class TimelineDefinedDatesUpdateEntityEventSubscriber implements EventSubscriberInterface {

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
   * @see \Drupal\omnipedia_core\Service\TimelineInterface::findDefinedDates()
   *   Calls this method to invoke a rescan of wiki nodes.
   */
  public function updateDefinedDates(BaseEntityEvent $event) {
    $entity = $event->getEntity();

    if (!$this->wiki->isWikiNode($entity)) {
      return;
    }

    $this->timeline->findDefinedDates();
  }

}
