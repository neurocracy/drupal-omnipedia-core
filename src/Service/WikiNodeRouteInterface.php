<?php

namespace Drupal\omnipedia_core\Service;

/**
 * The Omnipedia wiki node route service interface.
 */
interface WikiNodeRouteInterface {

  /**
   * Determine if the provided route name is considered viewing a wiki node.
   *
   * @param string $routeName
   *   The route name to check.
   *
   * @return boolean
   *   True if the route name is considered viewing a wiki node, or false
   *   otherwise.
   */
  public function isWikiNodeViewRouteName(string $routeName): bool;

}
