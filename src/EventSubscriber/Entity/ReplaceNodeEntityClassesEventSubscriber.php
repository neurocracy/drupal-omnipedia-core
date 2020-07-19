<?php

namespace Drupal\omnipedia_core\EventSubscriber\Entity;

use Drupal\core_event_dispatcher\Event\Entity\EntityTypeBuildEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Replaces core Node entity classes with our own.
 */
class ReplaceNodeEntityClassesEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_TYPE_BUILD => 'replaceNodeEntityClasses',
    ];
  }

  /**
   * Replace the core Node entity classes with our own.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityTypeBuildEvent $event
   *   The event object.
   *
   * @see \Drupal\omnipedia_core\Entity\Node
   *   Our extended Node entity class that we replace the core one with.
   *
   * @see \Drupal\omnipedia_core\Storage\NodeStorage
   *   Our extended Node storage class that we replace the core one with.
   */
  public function replaceNodeEntityClasses(EntityTypeBuildEvent $event): void {
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] */
    $entityTypes = $event->getEntityTypes();

    $entityTypes['node']
      ->setClass('Drupal\\omnipedia_core\\Entity\\Node')
      ->setStorageClass('Drupal\\omnipedia_core\\Storage\\NodeStorage');
  }

}
