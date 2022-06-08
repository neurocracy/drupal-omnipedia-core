<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\omnipedia_core\Service\WikiNodeRouteInterface;

/**
 * The Omnipedia wiki node route service.
 */
class WikiNodeRoute implements WikiNodeRouteInterface {

  /**
   * Route names that are considered as viewing a wiki node.
   *
   * Note that some of these need to be here to be considered when setting the
   * current date, as the current date needs to be updated even when not viewing
   * 'entity.node.canonical', for example, but also 'entity.node.edit_form' to
   * ensure any input filters that validate based on the date do so correctly.
   *
   * @var array
   */
  protected $wikiNodeViewRouteNames = [
    'entity.node.canonical',
    'entity.node.preview',
    'entity.node.edit_form',
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
