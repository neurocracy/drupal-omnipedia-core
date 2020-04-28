<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Service\WikiInterface;

/**
 * The Omnipedia wiki service.
 */
class Wiki implements WikiInterface {

  /**
   * The wiki node type.
   */
  protected const WIKI_NODE_TYPE = 'wiki_page';

  /**
   * The name of the date field on wiki nodes.
   */
  protected const WIKI_NODE_DATE_FIELD = 'field_date';

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
  private $stateManager;

  /**
   * The Drupal entity type plug-in manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructs this service object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    EntityTypeManagerInterface  $entityTypeManager,
    StateInterface              $stateManager
  ) {
    // Save dependencies.
    $this->entityTypeManager  = $entityTypeManager;
    $this->stateManager       = $stateManager;
  }

  /**
   * Ensure a provided parameter is a node, loading it if need be.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Either a node object, or null if one cannot be loaded.
   */
  protected function normalizeNode($node) {
    if (\is_object($node) && $node instanceof NodeInterface) {
      return $node;

    } else if (\is_numeric($node)) {
      /** @var \Drupal\node\NodeInterface|null */
      return $this->entityTypeManager->getStorage('node')->load($node);

    } else {
      return null;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeType(): string {
    return self::WIKI_NODE_TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function isWikiNode($node): bool {
    $node = $this->normalizeNode($node);

    if (\is_object($node) && $node instanceof NodeInterface) {
      return $node->getType() === $this->getWikiNodeType();

    } else {
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNode($node): ?NodeInterface {
    $node = $this->normalizeNode($node);

    if ($this->isWikiNode($node)) {
      return $node;
    } else {
      return null;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeDateFieldName(): string {
    return self::WIKI_NODE_DATE_FIELD;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeDate($node): ?string {
    $node = $this->getWikiNode($node);

    if ($node === null) {
      return null;
    }

    return $node->get($this->getWikiNodeDateFieldName())[0]->value;
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
   *   \Drupal\omnipedia_core\Service\WikiInterface::getTrackedWikiNodeData()
   *   for the required structure.
   *
   * @todo Add support for multiple languages.
   *
   * @see \Drupal\omnipedia_core\Service\WikiInterface::getTrackedWikiNodeData()
   *   Returns saved tracked data and describes the format for the $data array.
   */
  protected function setTrackedWikiNodeData(array $data): void {
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
  public function trackWikiNode($node): void {
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->getWikiNode($node);

    // Bail if no node could be loaded.
    if ($node === null) {
      return;
    }

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
    $nodeDate = $this->getWikiNodeDate($node);

    /** @var string */
    $nodeTitle = $node->getTitle();

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
  public function untrackWikiNode($node): void {
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->getWikiNode($node);

    // Bail if no node could be loaded.
    if ($node === null) {
      return;
    }

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

  /**
   * Resolve a node or title to all node IDs with the same title.
   *
   * @param \Drupal\node\NodeInterface|int|string $nodeOrTitle
   *   Must be one of the following:
   *
   *   - An instance of \Drupal\node\NodeInterface, i.e. a node object
   *
   *   - An integer or a numeric string that equates to a node ID
   *
   *   - A non-numeric string which is assumed to be a node title to search for
   *
   * @return array
   *   An array containing zero or more node IDs as values.
   */
  protected function nodeOrTitleToNids($nodeOrTitle): array {
    if (\is_string($nodeOrTitle)) {
      /** @var string */
      $title = $nodeOrTitle;

    } else {
      /** @var \Drupal\node\NodeInterface|null */
      $node = $this->normalizeNode($nodeOrTitle);

      if ($node instanceof NodeInterface) {
        /** @var string */
        $title = $node->getTitle();
      }
    }

    if (!isset($title)) {
      throw new \InvalidArgumentException('The $nodeOrTitle parameter must be a node object, an integer node ID, or a node title as a string.');
    }

    /** @var array */
    $nodeData = $this->getTrackedWikiNodeData();

    // Get all node IDs of nodes with this title.
    return \array_keys($nodeData['titles'], $title, true);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Move date sorting to $this->trackWikiNode() or
   *   $this->setTrackedWikiNodeData(), so that it doesn't need to be done every
   *   time this method is called.
   */
  public function getWikiNodeRevisions($nodeOrTitle): array {
    /** @var array */
    $nodeData = $this->getTrackedWikiNodeData();

    /** @var array */
    $nodes = [];

    /** @var array */
    $nids = $this->nodeOrTitleToNids($nodeOrTitle);

    foreach ($nids as $nid) {
      // The node's date parsed into an array of date parts. We do this so that
      // we don't need to have any knowledge of what format the date is in, just
      // as long as it can be parsed.
      /** @var array */
      $dateArray = \date_parse($nodeData['nodes'][$nid]['date']);

      $nodes[$nid] = [
        'nid'       => $nid,
        // We need to build DrupalDateTime objects so that we can use their
        // diff() method to sort by date. We later change this back to the
        // formatted string.
        'date'      => DrupalDateTime::createFromArray([
          'year'      => $dateArray['year'],
          'month'     => $dateArray['month'],
          'day'       => $dateArray['day'],
        ]),
        'title'     => $nodeData['nodes'][$nid]['title'],
        'published' => $nodeData['nodes'][$nid]['published'],
      ];
    }

    // Sort the array by their dates.
    \usort($nodes, function($a, $b) {
      // DrupalDateTime::diff() returns a \DateInterval object, which contains a
      // 'days' property:
      //
      // @see https://www.php.net/manual/en/class.dateinterval.php
      return $a['date']->diff($b['date'])->days * -1;
    });

    return $nodes;
  }

  /**
   * {@inheritdoc}
   */
  public function getWikiNodeRevision($nodeOrTitle, string $date): ?NodeInterface {
    // Get all node IDs of nodes with this title.
    /** @var array */
    $nids = $this->nodeOrTitleToNids($nodeOrTitle);

    /** @var array */
    $nodeData = $this->getTrackedWikiNodeData();

    // Loop through all found nodes and return the first one that has the date
    // we're looking for.
    foreach ($nids as $nid) {
      if ($nodeData['nodes'][$nid]['date'] !== $date) {
        continue;
      }

      return $this->normalizeNode($nid);
    }

    // No node with that date found.
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getMainPage(string $date): ?NodeInterface {
    return $this->getWikiNodeRevision('Main Page', $date);
  }

}
