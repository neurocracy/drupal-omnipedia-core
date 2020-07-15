<?php

namespace Drupal\omnipedia_core\EventSubscriber\EntityType;

use Drupal\hook_event_dispatcher\Event\EntityType\EntityTypeBuildEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Replaces the core Node entity class with our own.
 */
class ReplaceNodeEntityClassEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_TYPE_BUILD => 'replaceNodeEntityClass',
    ];
  }

  /**
   * Replace the core Node entity class with our own.
   *
   * @param \Drupal\hook_event_dispatcher\Event\EntityType\EntityTypeBuildEvent $event
   *   The event object.
   *
   * @see \Drupal\omnipedia_core\Entity\Node
   *   Our extended Node entity class that we replace the core one with.
   */
  public function replaceNodeEntityClass(EntityTypeBuildEvent $event): void {
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] */
    $entityTypes = $event->getEntityTypes();

    $entityTypes['node']->setClass('Drupal\\omnipedia_core\\Entity\\Node');
  }

}
