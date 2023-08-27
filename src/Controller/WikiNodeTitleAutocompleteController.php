<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route controller for wiki node title autocomplete.
 */
class WikiNodeTitleAutocompleteController implements ContainerInjectionInterface {

  /**
   * Constructs this controller; saves dependencies.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type manager.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly WikiNodeTrackerInterface   $wikiNodeTracker,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('omnipedia.wiki_node_tracker'),
    );
  }

  /**
   * Process autocomplete input for wiki node titles.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A Symfony request object containing an autocomplete query.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A Symfony JSON response containing any matching wiki node titles.
   */
  public function autocomplete(Request $request): JsonResponse {

    /** @var string */
    $input = Xss::filter($request->query->get('q'));

    /** @var array[] */
    $nodeData = $this->wikiNodeTracker->getTrackedWikiNodeData();

    /** @var string[] */
    $nids = ($this->entityTypeManager->getStorage('node')->getQuery())
      ->condition('type', WikiNodeInfo::TYPE)
      ->condition('title', $input, 'CONTAINS')
      ->accessCheck(true)
      ->execute();

    /** @var string[] */
    $titles = [];

    foreach ($nids as $revisionId => $nid) {

      // Skip any node IDs (nids) not present in the node data and any titles
      // that we already have.
      if (
        !isset($nodeData['titles'][$nid]) ||
        \in_array($nodeData['titles'][$nid], $titles)
      ) {
        continue;
      }

      $titles[] = $nodeData['titles'][$nid];

    }

    /** @var string[] */
    $matches = [];

    foreach ($titles as $title) {
      $matches[] = ['value' => $title, 'label' => $title];
    }

    return new JsonResponse($matches);

  }

}
