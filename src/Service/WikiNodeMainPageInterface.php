<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;

/**
 * The Omnipedia wiki node main page service interface.
 */
interface WikiNodeMainPageInterface {

  /**
   * Determine if a parameter is or equates to a main page wiki node.
   *
   * @param mixed $node
   *   A node entity object or a numeric value (integer or string) that equates
   *   to an existing node ID (nid) to load. Any other value will return false.
   *
   * @return boolean
   *   Returns true if the $node parameter is a main page wiki node or if it is
   *   a numeric value that equates to the ID of a main page wiki node; returns
   *   false otherwise.
   */
  public function isMainPage(mixed $node): bool;

  /**
   * Determine if the current route is a main page wiki node.
   *
   * @return boolean
   *   True if the current route is a main page wiki node; false otherwise.
   */
  public function isCurrentRouteMainPage(): bool;

  /**
   * Update the default main page.
   *
   * This is intended to be called whenever the site front page has been
   * changed, so that any stored data about the default main page can also be
   * updated.
   */
  public function updateDefaultMainPage(): void;

  /**
   * Get the main page node for the specified date.
   *
   * @param string $date
   *   Must be one of the following:
   *
   *   - A date string in the format stored in a wiki node's date field
   *
   *   - 'default': alias for the default main page as configured in the site
   *     configuration
   *
   * @return \Drupal\omnipedia_core\Entity\NodeInterface|null
   *   Returns the main page's node object for the specified date if it can be
   *   found; returns null otherwise.
   */
  public function getMainPage(string $date): ?WikiNodeInterface;

  /**
   * Get the main page route name.
   *
   * @return string
   *   The main page route name.
   */
  public function getMainPageRouteName(): string;

  /**
   * Get the main page route parameters.
   *
   * @param string $date
   *   The date to build the route parameters for. See self::getMainPage() for
   *   format.
   *
   * @return array
   *   The main page route parameters for the given date, or for the default
   *   main page if a main page does not exist for the given date.
   *
   * @see self::getMainPage()
   *   $date parameter format and options defined here.
   */
  public function getMainPageRouteParameters(string $date): array;

  /**
   * Get cache tags for all main pages.
   *
   * @return array
   *   Cache tags for all main pages and any additional data related to them.
   */
  public function getMainPagesCacheTags(): array;

}
