<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Session\AccountInterface;

/**
 * The Omnipedia wiki node access service interface.
 */
interface WikiNodeAccessInterface {

  /**
   * Determine if a user can access at least one wiki node.
   *
   * @param \Drupal\Core\Session\AccountInterface|null $user
   *   A user account to test access for. If this is null, the current user will
   *   be used.
   *
   * @return bool
   *   True if the user can access at least one wiki node and false otherwise.
   */
  public function canUserAccessAnyWikiNode(
    ?AccountInterface $user = null
  ): bool;

}
