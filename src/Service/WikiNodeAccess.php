<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\omnipedia_core\Entity\Node;
use Drupal\omnipedia_core\Service\WikiNodeAccessInterface;

/**
 * The Omnipedia wiki node access service interface.
 */
class WikiNodeAccess implements WikiNodeAccessInterface {

  /**
   * The Drupal account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected AccountSwitcherInterface $accountSwitcher;

  /**
   * The current user proxy service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * The Drupal node entity storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * Constructs this service object; saves dependencies.
   *
   * @param \Drupal\Core\Session\AccountSwitcherInterface $accountSwitcher
   *   The Drupal account switcher service.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user proxy service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type manager.
   */
  public function __construct(
    AccountSwitcherInterface    $accountSwitcher,
    AccountProxyInterface       $currentUser,
    EntityTypeManagerInterface  $entityTypeManager
  ) {
    $this->accountSwitcher  = $accountSwitcher;
    $this->currentUser      = $currentUser;
    $this->nodeStorage      = $entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   *
   * @todo This needs to be properly reworked to account for Permissions by
   *   Term and other node access checks.
   */
  public function canUserAccessAnyWikiNode(
    ?AccountInterface $user = null
  ): bool {

    // Return false if we didn't get a user account to check.
    if (!\is_object($user)) {
      return false;
    }

    // If the user does not have the access content permission, just return
    // false here. This saves some work and also catches cases that the entity
    // query does not.
    if (!$user->hasPermission('access content')) {
      return false;
    }

    // Switch over to the provided user account for access checking.
    $this->accountSwitcher->switchTo($user);

    /** @var \Drupal\Core\Entity\Query\QueryInterface The node count query; note that this obeys access checking for the current user. */
    $query = ($this->nodeStorage->getQuery())
      ->condition('type', Node::getWikiNodeType())
      ->count();

    /** @var int */
    $count = (int) $query->execute();

    /** @var bool True if the count query returned at least one result and false otherwise. */
    $return = $count > 0;

    // Switch back to the current user if we were provided a user to test access
    // for.
    $this->accountSwitcher->switchBack();

    return $return;

  }

}
