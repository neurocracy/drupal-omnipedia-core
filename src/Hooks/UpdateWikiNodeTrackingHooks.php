<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Hooks;

use Drupal\Core\Entity\EntityInterface;
use Drupal\hux\Attribute\Hook;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;

/**
 * Hooks to update wiki node tracking.
 *
 * This works around edge cases where tracking can get out of sync by
 * untracking and then tracking a wiki node again when it's updated.
 */
class UpdateWikiNodeTrackingHooks {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   */
  public function __construct(
    protected readonly WikiNodeResolverInterface  $wikiNodeResolver,
    protected readonly WikiNodeTrackerInterface   $wikiNodeTracker,
  ) {}

  /**
   * Untrack and re-track wiki nodes when they're updated.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity object.
   */
  #[Hook('entity_update')]
  public function entityUpdate(EntityInterface $entity): void {

    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveWikiNode($entity);

    if (!\is_object($node)) {
      return;
    }

    $this->wikiNodeTracker->untrackWikiNode($node);

    $this->wikiNodeTracker->trackWikiNode($node);

  }

}
