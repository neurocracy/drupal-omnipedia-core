<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
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
  protected $accountSwitcher;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Drupal node entity storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructs this service object; saves dependencies.
   *
   * @param \Drupal\Core\Session\AccountSwitcherInterface $accountSwitcher
   *   The Drupal account switcher service.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type manager.
   */
  public function __construct(
    AccountSwitcherInterface    $accountSwitcher,
    AccountInterface            $currentUser,
    EntityTypeManagerInterface  $entityTypeManager
  ) {
    $this->accountSwitcher  = $accountSwitcher;
    $this->currentUser      = $currentUser;
    $this->nodeStorage      = $entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function canUserAccessAnyWikiNode(
    ?AccountInterface $user = null
  ): bool {

    // Switch over to the provided user account for access checking.
    if (\is_object($user)) {
      $this->accountSwitcher->switchTo($user);
    }

    /** @var \Drupal\Core\Entity\Query\QueryInterface The node count query; note that this obeys access checking for the current user. */
    $query = ($this->nodeStorage->getQuery())
      ->condition('type', Node::getWikiNodeType())
      ->count();

    /** @var bool True if the count query returned at least one result and false otherwise. */
    $return = (int) $query->execute() > 0;

    // Switch back to the current user if we were provided a user to test access
    // for.
    if (\is_object($user)) {
      $this->accountSwitcher->switchBack();
    }

    return $return;

  }

}
