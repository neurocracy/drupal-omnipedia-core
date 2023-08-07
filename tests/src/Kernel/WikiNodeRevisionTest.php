<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Kernel;

use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\Tests\omnipedia_core\Kernel\WikiNodeKernelTestBase;

/**
 * Tests for the Omnipedia wiki node revision service.
 *
 * @group omnipedia
 *
 * @group omnipedia_core
 */
class WikiNodeRevisionTest extends WikiNodeKernelTestBase {

  /**
   * The Omnipedia wiki node revision service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface
   */
  protected readonly WikiNodeRevisionInterface $wikiNodeRevision;

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected readonly WikiNodeTrackerInterface $wikiNodeTracker;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->wikiNodeRevision = $this->container->get(
      'omnipedia.wiki_node_revision'
    );

    $this->wikiNodeTracker = $this->container->get(
      'omnipedia.wiki_node_tracker'
    );

  }

  /**
   * Data provider for wiki node revision data.
   *
   * @return array
   */
  public function getWikiNodeRevisionsDataProvider(): array {

    return [
      [
        'node_values' => [
          [
            'nid' => 1, 'title' => 'Page 1', 'date' => '2049-09-28',
            'status' => WikiNodeInterface::NOT_PUBLISHED,
          ],
          [
            'nid' => 2, 'title' => 'Page 2', 'date' => '2049-09-28',
            'status' => WikiNodeInterface::NOT_PUBLISHED,
          ],
          [
            'nid' => 3, 'title' => 'Page 1', 'date' => '2049-09-29',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 4, 'title' => 'Page 2', 'date' => '2049-09-29',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
        ],
        'queries' => [
          ['query' => 'Page 1', 'expected' => [
            1 => ['nid' => 1, 'title' => 'Page 1', 'date' => '2049-09-28', 'published' => false],
            3 => ['nid' => 3, 'title' => 'Page 1', 'date' => '2049-09-29', 'published' => true],
          ]],
          ['query' => 'Page 2', 'expected' => [
            2 => ['nid' => 2, 'title' => 'Page 2', 'date' => '2049-09-28', 'published' => false],
            4 => ['nid' => 4, 'title' => 'Page 2', 'date' => '2049-09-29', 'published' => true],
          ]],
          ['query' => 1, 'expected' => [
            1 => ['nid' => 1, 'title' => 'Page 1', 'date' => '2049-09-28', 'published' => false],
            3 => ['nid' => 3, 'title' => 'Page 1', 'date' => '2049-09-29', 'published' => true],
          ]],
          ['query' => 2, 'expected' => [
            2 => ['nid' => 2, 'title' => 'Page 2', 'date' => '2049-09-28', 'published' => false],
            4 => ['nid' => 4, 'title' => 'Page 2', 'date' => '2049-09-29', 'published' => true],
          ]],
          ['query' => 3, 'expected' => [
            1 => ['nid' => 1, 'title' => 'Page 1', 'date' => '2049-09-28', 'published' => false],
            3 => ['nid' => 3, 'title' => 'Page 1', 'date' => '2049-09-29', 'published' => true],
          ]],
          ['query' => 4, 'expected' => [
            2 => ['nid' => 2, 'title' => 'Page 2', 'date' => '2049-09-28', 'published' => false],
            4 => ['nid' => 4, 'title' => 'Page 2', 'date' => '2049-09-29', 'published' => true],
          ]],
        ],
      ],
      [
        'node_values' => [
          [
            'nid' => 1, 'title' => 'Page 1', 'date' => '2049-09-28',
            'status' => WikiNodeInterface::NOT_PUBLISHED,
          ],
          [
            'nid' => 2, 'title' => 'Page 2', 'date' => '2049-09-28',
            'status' => WikiNodeInterface::NOT_PUBLISHED,
          ],
          [
            'nid' => 3, 'title' => 'Page 1', 'date' => '2049-09-29',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 4, 'title' => 'Page 2', 'date' => '2049-09-29',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 5, 'title' => 'Page 1', 'date' => '2049-09-30',
            'status' => WikiNodeInterface::NOT_PUBLISHED,
          ],
          [
            'nid' => 6, 'title' => 'Page 2', 'date' => '2049-09-30',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 7, 'title' => 'Page 3', 'date' => '2049-09-30',
            'status' => WikiNodeInterface::NOT_PUBLISHED,
          ],
          [
            'nid' => 8, 'title' => 'Page 1', 'date' => '2049-10-01',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 9, 'title' => 'Page 2', 'date' => '2049-10-01',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 10, 'title' => 'Page 3', 'date' => '2049-10-01',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
        ],
        'queries' => [
          ['query' => 'Page 1', 'expected' => [
            1 => ['nid' => 1, 'title' => 'Page 1', 'date' => '2049-09-28', 'published' => false],
            3 => ['nid' => 3, 'title' => 'Page 1', 'date' => '2049-09-29', 'published' => true],
            5 => ['nid' => 5, 'title' => 'Page 1', 'date' => '2049-09-30', 'published' => false],
            8 => ['nid' => 8, 'title' => 'Page 1', 'date' => '2049-10-01', 'published' => true],
          ]],
          ['query' => 'Page 2', 'expected' => [
            2 => ['nid' => 2, 'title' => 'Page 2', 'date' => '2049-09-28', 'published' => false],
            4 => ['nid' => 4, 'title' => 'Page 2', 'date' => '2049-09-29', 'published' => true],
            6 => ['nid' => 6, 'title' => 'Page 2', 'date' => '2049-09-30', 'published' => true],
            9 => ['nid' => 9, 'title' => 'Page 2', 'date' => '2049-10-01', 'published' => true],
          ]],
          ['query' => 'Page 3', 'expected' => [
            7  => ['nid' => 7,  'title' => 'Page 3', 'date' => '2049-09-30', 'published' => false],
            10 => ['nid' => 10, 'title' => 'Page 3', 'date' => '2049-10-01', 'published' => true],
          ]],
          ['query' => 1, 'expected' => [
            1 => ['nid' => 1, 'title' => 'Page 1', 'date' => '2049-09-28', 'published' => false],
            3 => ['nid' => 3, 'title' => 'Page 1', 'date' => '2049-09-29', 'published' => true],
            5 => ['nid' => 5, 'title' => 'Page 1', 'date' => '2049-09-30', 'published' => false],
            8 => ['nid' => 8, 'title' => 'Page 1', 'date' => '2049-10-01', 'published' => true],
          ]],
          ['query' => 2, 'expected' => [
            2 => ['nid' => 2, 'title' => 'Page 2', 'date' => '2049-09-28', 'published' => false],
            4 => ['nid' => 4, 'title' => 'Page 2', 'date' => '2049-09-29', 'published' => true],
            6 => ['nid' => 6, 'title' => 'Page 2', 'date' => '2049-09-30', 'published' => true],
            9 => ['nid' => 9, 'title' => 'Page 2', 'date' => '2049-10-01', 'published' => true],
          ]],
          ['query' => 5, 'expected' => [
            1 => ['nid' => 1, 'title' => 'Page 1', 'date' => '2049-09-28', 'published' => false],
            3 => ['nid' => 3, 'title' => 'Page 1', 'date' => '2049-09-29', 'published' => true],
            5 => ['nid' => 5, 'title' => 'Page 1', 'date' => '2049-09-30', 'published' => false],
            8 => ['nid' => 8, 'title' => 'Page 1', 'date' => '2049-10-01', 'published' => true],
          ]],
          ['query' => 9, 'expected' => [
            2 => ['nid' => 2, 'title' => 'Page 2', 'date' => '2049-09-28', 'published' => false],
            4 => ['nid' => 4, 'title' => 'Page 2', 'date' => '2049-09-29', 'published' => true],
            6 => ['nid' => 6, 'title' => 'Page 2', 'date' => '2049-09-30', 'published' => true],
            9 => ['nid' => 9, 'title' => 'Page 2', 'date' => '2049-10-01', 'published' => true],
          ]],
          ['query' => 7, 'expected' => [
            7  => ['nid' => 7,  'title' => 'Page 3', 'date' => '2049-09-30', 'published' => false],
            10 => ['nid' => 10, 'title' => 'Page 3', 'date' => '2049-10-01', 'published' => true],
          ]],
        ],
      ],
    ];

  }

  /**
   * Data provider for wiki node revision data.
   *
   * @return array
   */
  public function getWikiNodeRevisionsNonExistentDataProvider(): array {

    /** @var array */
    $data = $this->getWikiNodeRevisionsDataProvider();

    $data[0]['queries'] = [
      // These have valid queries but the dates won't be found.
      ['query' => 'Page 1', 'date' => '2049-09-27'],
      ['query' => 'Page 1', 'date' => '2049-09-30'],
      ['query' => 'Page 2', 'date' => '2049-12-01'],
      ['query' => 'Page 2', 'date' => '2049-10-01'],
      ['query' => 1, 'date' => '2049-09-27'],
      ['query' => 2, 'date' => '2049-09-27'],
      ['query' => 3, 'date' => '2049-10-01'],
      ['query' => 4, 'date' => '2049-10-02'],
      // These have valid dates but the query won't be found.
      ['query' => 5, 'date' => '2049-09-28',
        'exception' => \InvalidArgumentException::class,
      ],
      ['query' => 100, 'date' => '2049-09-28',
        'exception' => \InvalidArgumentException::class,
      ],
      ['query' => 'Page 3',  'date' => '2049-09-28',
        'exception' => \InvalidArgumentException::class,
      ],
    ];

    $data[1]['queries'] = [
      // These have valid queries but the dates won't be found.
      ['query' => 'Page 1', 'date' => '2049-09-27'],
      ['query' => 'Page 1', 'date' => '2049-10-02'],
      ['query' => 'Page 2', 'date' => '2049-09-27'],
      ['query' => 'Page 2', 'date' => '2049-10-02'],
      ['query' => 1, 'date' => '2049-09-27'],
      ['query' => 2, 'date' => '2049-09-27'],
      ['query' => 3, 'date' => '2049-10-02'],
      ['query' => 4, 'date' => '2049-10-02'],
      // These have valid dates but the query won't be found.
      ['query' => 50, 'date' => '2049-09-28',
        'exception' => \InvalidArgumentException::class,
      ],
      ['query' => 51, 'date' => '2049-09-28',
        'exception' => \InvalidArgumentException::class,
      ],
      ['query' => 'Page 100', 'date' => '2049-09-28',
        'exception' => \InvalidArgumentException::class,
      ],
    ];

    return $data;

  }

  /**
   * Test the getWikiNodeRevisions() method.
   *
   * @dataProvider getWikiNodeRevisionsDataProvider
   */
  public function testGetWikiNodeRevisions(
    array $nodesInfo, array $queries,
  ): void {

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface[] The created node objects, keyed by their integer node IDs. */
    $nodes = [];

    foreach ($nodesInfo as $nodeInfo) {

      /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
      $wikiNode = $this->drupalCreateWikiNode([
        'nid'     => $nodeInfo['nid'],
        'title'   => $nodeInfo['title'],
        'status'  => $nodeInfo['status'],
      ], $nodeInfo['date']);

      $this->wikiNodeTracker->trackWikiNode($wikiNode);

      $nodes[(int) $wikiNode->nid->getString()] = $wikiNode;

    }

    foreach ($queries as $item) {

      $this->assertEquals(
        $item['expected'],
        $this->wikiNodeRevision->getWikiNodeRevisions($item['query']),
      );

      // Try passing a created node object if the query is an integer that
      // equates to a key that exists in the $nodes array.
      if (!\is_int($item['query']) || !isset($nodes[$item['query']])) {
        continue;
      }

      $this->assertEquals(
        $item['expected'],
        $this->wikiNodeRevision->getWikiNodeRevisions($nodes[$item['query']]),
      );

    }

  }

  /**
   * Test the getWikiNodeRevision() method.
   *
   * @dataProvider getWikiNodeRevisionsDataProvider
   */
  public function testGetWikiNodeRevision(
    array $nodesInfo, array $queries,
  ): void {

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface[] The created node objects, keyed by their integer node IDs. */
    $nodes = [];

    foreach ($nodesInfo as $nodeInfo) {

      /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
      $wikiNode = $this->drupalCreateWikiNode([
        'nid'     => $nodeInfo['nid'],
        'title'   => $nodeInfo['title'],
        'status'  => $nodeInfo['status'],
      ], $nodeInfo['date']);

      $this->wikiNodeTracker->trackWikiNode($wikiNode);

      $nodes[(int) $wikiNode->nid->getString()] = $wikiNode;

    }

    foreach ($queries as $item) {

      // We stretch the definition of the 'expected' key slightly so that we can
      // reuse the same data provider as self::testGetWikiNodeRevisions().
      foreach ($item['expected'] as $nid => $expectedItem) {

        /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
        $revision = $this->wikiNodeRevision->getWikiNodeRevision(
          $item['query'], $expectedItem['date'],
        );

        // Assert that we got an object and not null.
        $this->assertIsObject($revision);

        // Assert that the node ID from the returned node object matches the
        // expected nid.
        $this->assertEquals($nid, (int) $revision->nid->getString());

        // Try passing a created node object if the query is an integer that
        // equates to a key that exists in the $nodes array.
        if (!\is_int($item['query']) || !isset($nodes[$item['query']])) {
          continue;
        }

        /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
        $revision = $this->wikiNodeRevision->getWikiNodeRevision(
          $nodes[$item['query']], $expectedItem['date'],
        );

        // Assert that we got an object and not null.
        $this->assertIsObject($revision);

        $this->assertEquals($nid, (int) $revision->nid->getString());

      }

    }

  }

  /**
   * Test the getWikiNodeRevision() method with non-existent data.
   *
   * @dataProvider getWikiNodeRevisionsNonExistentDataProvider
   */
  public function testGetWikiNodeRevisionNonExistent(
    array $nodesInfo, array $queries,
  ): void {

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface[] The created node objects, keyed by their integer node IDs. */
    $nodes = [];

    foreach ($nodesInfo as $nodeInfo) {

      /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
      $wikiNode = $this->drupalCreateWikiNode([
        'nid'     => $nodeInfo['nid'],
        'title'   => $nodeInfo['title'],
        'status'  => $nodeInfo['status'],
      ], $nodeInfo['date']);

      $this->wikiNodeTracker->trackWikiNode($wikiNode);

      $nodes[(int) $wikiNode->nid->getString()] = $wikiNode;

    }

    foreach ($queries as $item) {

      // Some of these will throw an exception so expect the ones explicitly
      // marked as such.
      if (isset($item['exception'])) {
        $this->expectException($item['exception']);
      }

      $this->assertNull($this->wikiNodeRevision->getWikiNodeRevision(
        $item['query'], $item['date'],
      ));

    }

  }

}
