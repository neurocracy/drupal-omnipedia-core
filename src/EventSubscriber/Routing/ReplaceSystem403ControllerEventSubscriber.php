<?php

namespace Drupal\omnipedia_core\EventSubscriber\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Event subscriber to replace the 'system.403' route controller.
 *
 * @see https://www.drupal.org/docs/8/api/routing-system/altering-existing-routes-and-adding-new-routes-based-on-dynamic-ones
 */
class ReplaceSystem403ControllerEventSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    /** @var \Symfony\Component\Routing\Route|null */
    $route = $collection->get('system.403');

    if ($route === null) {
      return;
    }

    // Replace the existing controller method with our own.
    //
    // @todo Should we check if the Drupal core method has not been replaced by
    // some other module?
    $route->setDefault(
      '_controller',
      'Drupal\\omnipedia_core\\Controller\\Http4xxController::on403'
    );
  }

}
