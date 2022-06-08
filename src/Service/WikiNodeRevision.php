<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;

/**
 * The Omnipedia wiki node revision service.
 */
class WikiNodeRevision implements WikiNodeRevisionInterface {

  /**
   * The Omnipedia wiki node resolver service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeResolverInterface
   */
  protected $wikiNodeResolver;

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected $wikiNodeTracker;

  /**
   * Constructs this service object.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   */
  public function __construct(
    WikiNodeResolverInterface $wikiNodeResolver,
    WikiNodeTrackerInterface  $wikiNodeTracker
  ) {
    // Save dependencies.
    $this->wikiNodeResolver = $wikiNodeResolver;
    $this->wikiNodeTracker  = $wikiNodeTracker;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Move array intersection/nids by date stuff to
   *   \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::trackWikiNode()
   *   to store the sorted array so that it doesn't need to be done every time
   *   this method is called.
   */
  public function getWikiNodeRevisions($nodeOrTitle): array {
    /** @var array */
    $nodeData = $this->wikiNodeTracker->getTrackedWikiNodeData();

    /** @var array */
    $nodes = [];

    /** @var array */
    $nids = $this->wikiNodeResolver->nodeOrTitleToNids($nodeOrTitle);

    foreach ($nodeData['dates'] as $date => $nodesForDate) {
      // Determine if any of the node IDs are present in this date.
      /** @var array */
      $intersected = \array_intersect($nodesForDate, $nids);

      // Skip if no nid was found via \array_intersect().
      if (\count($intersected) === 0) {
        continue;
      }

      // Since there should only ever be one nid per date, we can just get the
      // value of the first index.
      /** @var int */
      $nid = (int) \reset($intersected);

      /** @var array */
      $nodes[$nid] = [
        'nid'       => $nid,
        'date'      => $nodeData['nodes'][$nid]['date'],
        'title'     => $nodeData['nodes'][$nid]['title'],
        'published' => $nodeData['nodes'][$nid]['published'],
      ];
    }

    return $nodes;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeRevision($nodeOrTitle, string $date): ?WikiNodeInterface {
    // Get all node IDs of nodes with this title.
    /** @var array */
    $nids = $this->wikiNodeResolver->nodeOrTitleToNids($nodeOrTitle);

    /** @var array */
    $nodeData = $this->wikiNodeTracker->getTrackedWikiNodeData();

    // Loop through all found nodes and return the first one that has the date
    // we're looking for.
    foreach ($nids as $nid) {
      if ($nodeData['nodes'][$nid]['date'] !== $date) {
        continue;
      }

      return $this->wikiNodeResolver->resolveNode($nid);
    }

    // No node with that date found.
    return null;
  }

}
