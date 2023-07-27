<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\State\StateInterface;
use Drupal\omnipedia_core\Entity\NodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;

/**
 * The Omnipedia wiki node tracker service.
 */
class WikiNodeTracker implements WikiNodeTrackerInterface {

  /**
   * The Drupal state key where we store wiki node information.
   */
  protected const WIKI_NODE_INFO_STATE_KEY = 'omnipedia.wiki_node_info';

  /**
   * The Drupal state key where we store wiki node nids by their dates.
   */
  protected const WIKI_NODE_DATES_STATE_KEY = 'omnipedia.wiki_node_dates';

  /**
   * The Drupal state key where we store wiki node nids and their titles.
   */
  protected const WIKI_NODE_TITLES_STATE_KEY = 'omnipedia.wiki_node_titles';

  /**
   * The Drupal state system manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $stateManager;

  /**
   * Constructs this service object.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    StateInterface $stateManager
  ) {
    // Save dependencies.
    $this->stateManager = $stateManager;
  }

  /**
   * {@inheritdoc}
   *
   * @see $this->setTrackedWikiNodeData()
   *   Saves data.
   */
  public function getTrackedWikiNodeData(): array {
    /** @var array|null */
    $infoStateData = $this->stateManager->get(self::WIKI_NODE_INFO_STATE_KEY);

    /** @var array|null */
    $datesStateData = $this->stateManager->get(self::WIKI_NODE_DATES_STATE_KEY);

    /** @var array|null */
    $titlesStateData = $this->stateManager
      ->get(self::WIKI_NODE_TITLES_STATE_KEY);

    /** @var array */
    $data = [];

    foreach ([
      'nodes'   => $infoStateData,
      'dates'   => $datesStateData,
      'titles'  => $titlesStateData,
    ] as $dataKey => $stateData) {
      // If state data doesn't exist for this, set it to an empty array.
      if (!\is_array($stateData)) {
        $data[$dataKey] = [];

        continue;
      }

      $data[$dataKey] = $stateData;
    }

    return $data;
  }

  /**
   * Save tracked wiki node data.
   *
   * @param array $data
   *   An array of data to save. See
   *   \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::getTrackedWikiNodeData()
   *   for the required structure.
   *
   * @todo Add support for multiple languages.
   *
   * @see \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface::getTrackedWikiNodeData()
   *   Returns saved tracked data and describes the format for the $data array.
   */
  protected function setTrackedWikiNodeData(array $data): void {
    /** @var array */
    $datesSorted = [];

    // Parse the provided dates into DrupalDateTime objects so that they can be
    // sorted chronologically.
    foreach ($data['dates'] as $date => $dateNodes) {

      // Skip dates that don't have any nodes any longer. This can occur when
      // the last node for a date is deleted or changed to a different date.
      if (empty($dateNodes)) {
        continue;
      }

      // The date parsed into an array of date parts. We do this so that we
      // don't need to have any knowledge of what format the date is in, just as
      // long as it can be parsed.
      /** @var array */
      $dateArray = \date_parse($date);

      // We need both DrupalDateTime objects and the string date, as \usort()
      // will re-index the array, removing our keys.
      $datesSorted[$date] = [
        'date'      => $date,
        'datetime'  => DrupalDateTime::createFromArray([
          'year'  => $dateArray['year'],
          'month' => $dateArray['month'],
          'day'   => $dateArray['day'],
        ]),
      ];
    }

    // Sort the array chronologically.
    \usort($datesSorted, function($a, $b) {
      // As of PHP 5.2.2, DateTime objects can be compared using comparison
      // operators, so we do that to sort them chronologically:
      //
      // @see https://www.php.net/manual/en/datetime.diff.php
      return ($a['datetime'] < $b['datetime'] ? 1 : -1) * -1;
    });

    // Save the unsorted dates as we're going to empty the array.
    /** @var array */
    $dateDataUnsorted = $data['dates'];

    /** @var array */
    $data['dates'] = [];

    // Copy the unsorted date data into the $data array in chronologically
    // sorted order.
    foreach ($datesSorted as $dateSorted) {
      $data['dates'][$dateSorted['date']] =
        $dateDataUnsorted[$dateSorted['date']];
    }

    foreach ([
      'nodes'   => self::WIKI_NODE_INFO_STATE_KEY,
      'dates'   => self::WIKI_NODE_DATES_STATE_KEY,
      'titles'  => self::WIKI_NODE_TITLES_STATE_KEY,
    ] as $dataKey => $stateKey) {
      // Save to state storage.
      $this->stateManager->set($stateKey, $data[$dataKey]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function trackWikiNode(NodeInterface $node): void {
    /** @var array */
    $data = $this->getTrackedWikiNodeData();

    /** @var string */
    $nid = $node->nid->getString();

    // If the node already exists in the saved data, we have to remove the
    // existing node information so that we can add the updated information.
    if (isset($data['nodes'][$nid])) {
      // Remove the node from the 'dates' records.
      foreach ($data['dates'] as $date => &$nodes) {
        $datesKey = \array_search($nid, $nodes);

        if ($datesKey === false) {
          continue;
        }

        // Remove the node from the array.
        //
        // @see https://stackoverflow.com/a/369608
        \array_splice($nodes, $datesKey, 1);

        break;
      }
    }

    // Now we add the data for the node, which works for both existing nodes and
    // newly created ones.

    /** @var string */
    $nodeTitle = $node->getTitle();

    /** @var string */
    $nodeDate = $node->getWikiNodeDate();

    $data['nodes'][$nid] = [
      'date'      => $nodeDate,
      'title'     => $nodeTitle,
      'published' => $node->isPublished(),
    ];

    $data['dates'][$nodeDate][] = $nid;

    $data['titles'][$nid] = $nodeTitle;

    $this->setTrackedWikiNodeData($data);
  }

  /**
   * {@inheritdoc}
   */
  public function untrackWikiNode(NodeInterface $node): void {
    /** @var array */
    $data = $this->getTrackedWikiNodeData();

    /** @var int */
    $nid = (int) $node->nid->getString();

    /** @var string */
    $nodeDate = $data['nodes'][$nid]['date'];

    /** @var string */
    $nodeTitle = $data['nodes'][$nid]['title'];

    if (!isset($data['nodes'][$nid])) {
      return;
    }

    /** @var int */
    $datesKey = \array_search($nid, $data['dates'][$nodeDate]);

    // Remove from the 'dates' array.
    //
    // @see https://stackoverflow.com/a/369608
    \array_splice($data['dates'][$nodeDate], $datesKey, 1);

    // Remove from the 'titles' array.
    unset($data['titles'][$nid]);

    // Remove from the 'nodes' array.
    unset($data['nodes'][$nid]);

    $this->setTrackedWikiNodeData($data);
  }

}
