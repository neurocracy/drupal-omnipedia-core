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
   * Test the getWikiNodeRevisions() method.
   *
   * @dataProvider getWikiNodeRevisionsDataProvider
   */
  public function testGetWikiNodeRevisions(
    array $nodesInfo, array $queries,
  ): void {

    foreach ($nodesInfo as $nodeInfo) {

      /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
      $wikiNode = $this->drupalCreateWikiNode([
        'nid'     => $nodeInfo['nid'],
        'title'   => $nodeInfo['title'],
        'status'  => $nodeInfo['status'],
      ], $nodeInfo['date']);

      $this->wikiNodeTracker->trackWikiNode($wikiNode);

    }

    foreach ($queries as $item) {

      $this->assertEquals(
        $item['expected'],
        $this->wikiNodeRevision->getWikiNodeRevisions($item['query']),
      );

    }

  }

}
