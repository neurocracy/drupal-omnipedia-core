<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\omnipedia_core\Service\WikiNodeRouteInterface;

/**
 * The Omnipedia wiki node route service.
 */
class WikiNodeRoute implements WikiNodeRouteInterface {

  /**
   * Route names that are considered as viewing a wiki node.
   *
   * @var array
   */
  protected $wikiNodeViewRouteNames = [
    'entity.node.canonical',
    'entity.node.preview',
    'entity.node.omnipedia_changes',
  ];

  /**
   * {@inheritdoc}
   */
  public function isWikiNodeViewRouteName(?string $routeName): bool {
    if ($routeName === null) {
      return false;
    }

    return \in_array($routeName, $this->wikiNodeViewRouteNames);
  }

}
