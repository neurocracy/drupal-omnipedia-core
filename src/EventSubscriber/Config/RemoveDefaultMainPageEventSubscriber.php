<?php

namespace Drupal\omnipedia_core\EventSubscriber\Config;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\State\StateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\omnipedia_core\Service\Wiki;

/**
 * Event subscriber to remove stored default main page when config is updated.
 */
class RemoveDefaultMainPageEventSubscriber implements EventSubscriberInterface {

  /**
   * The Drupal state system manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $stateManager;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    StateInterface $stateManager
  ) {
    $this->stateManager = $stateManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'configSave',
    ];
  }

  /**
   * This deletes the stored default main page when system.site is updated.
   *
   * The wiki service will rebuild the value upon failing to read it from the
   * site state when it's next asked to fetch it.
   *
   * Note that this has been successfully tested with configuration import via
   * Drush, but may not work as expected if system.site is changed via a
   * hook_update_N() hook.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Drupal configuration CRUD event object.
   *
   * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Extension%21module.api.php/function/hook_update_N
   *   Documentation for hook_update_N() describing the limitations of what is
   *   safe during that hook. Unclear if the state manager is considered a safe
   *   operation.
   *
   * @todo Since this isn't intended to be run via a hook_update_N(), is there
   *   some sort of check we can have to bail if this is invoked during a
   *   hook_update_N()?
   *
   * @todo Can we use the omnipedia.wiki service directly via dependency
   *   injection and add a method to delete the stored default main page so that
   *   this handler doesn't have to know about the constant?
   */
  public function configSave(ConfigCrudEvent $event): void {
    // Bail if this wasn't the system.site config that changed or this is
    // system.site but the page.front key hasn't been changed.
    if (
      $event->getConfig()->getName() !== 'system.site' ||
      !$event->isChanged('page.front')
    ) {
      return;
    }

    $this->stateManager->delete(Wiki::DEFAULT_MAIN_PAGE_STATE_KEY);
  }

}
