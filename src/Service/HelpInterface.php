<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Render\MarkupInterface;

/**
 * Omnipedia help service interface.
 *
 * @deprecated in 5.x and is removed from 6.x. Use @link
 *   https://github.com/neurocracy/omnipedia/issues/39 the Hux module @endlink
 *   for object-oriented hooks instead.
 */
interface HelpInterface {

  /**
   * Generate help content for a given route.
   *
   * @param string $routeName
   *   The current route.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match. This can be used to generate different help
   *   output for different pages that share the same route.
   *
   * @return Drupal\Component\Render\MarkupInterface|array|string
   *   The help content in the form of a render array, a localized string, or an
   *   object that can be rendered into a string.
   *
   * @see \hook_help()
   */
  public function help(
    string $routeName, RouteMatchInterface $routeMatch
  ): MarkupInterface|array|string;

}
