<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Kernel;

use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\Tests\omnipedia_core\Kernel\WikiNodeKernelTestBase;

/**
 * Tests for the Omnipedia wiki node tracker service.
 *
 * @group omnipedia
 *
 * @group omnipedia_core
 */
class WikiNodeTrackerTest extends WikiNodeKernelTestBase {

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected readonly WikiNodeTrackerInterface $wikiNodeTracker;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->wikiNodeTracker = $this->container->get(
      'omnipedia.wiki_node_tracker'
    );

  }

  /**
   * Data provider for tracking wiki node data.
   *
   * @return array
   *
   * @todo Rework this as an iterator so that we can generate this
   *   programmatically and not have to write it all out.
   *
   * @see https://docs.phpunit.de/en/9.6/writing-tests-for-phpunit.html#data-providers
   */
  public function trackWikiNodeDataProvider(): array {

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
        'expected' => [
          'dates' => [
            '2049-09-28' => ['1', '2'],
            '2049-09-29' => ['3', '4'],
          ],
          'nodes' => [
            1  => ['title' => 'Page 1', 'date' => '2049-09-28', 'published' => false],
            2  => ['title' => 'Page 2', 'date' => '2049-09-28', 'published' => false],

            3  => ['title' => 'Page 1', 'date' => '2049-09-29', 'published' => true],
            4  => ['title' => 'Page 2', 'date' => '2049-09-29', 'published' => true],
          ],
          'titles' => [
            1 => 'Page 1',
            2 => 'Page 2',
            3 => 'Page 1',
            4 => 'Page 2',
          ],
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
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 6, 'title' => 'Page 2', 'date' => '2049-09-30',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 7, 'title' => 'Page 3', 'date' => '2049-09-30',
            'status' => WikiNodeInterface::PUBLISHED,
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
          [
            'nid' => 11, 'title' => 'Page 1', 'date' => '2049-10-02',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 12, 'title' => 'Page 2', 'date' => '2049-10-02',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 13, 'title' => 'Page 3', 'date' => '2049-10-02',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
          [
            'nid' => 14, 'title' => 'Page 4', 'date' => '2049-10-02',
            'status' => WikiNodeInterface::PUBLISHED,
          ],
        ],
        'expected' => [
          'dates' => [
            '2049-09-28' => ['1',  '2'],
            '2049-09-29' => ['3',  '4'],
            '2049-09-30' => ['5',  '6',  '7'],
            '2049-10-01' => ['8',  '9',  '10'],
            '2049-10-02' => ['11', '12', '13', '14'],
          ],
          'nodes' => [
            1  => ['title' => 'Page 1', 'date' => '2049-09-28', 'published' => false],
            2  => ['title' => 'Page 2', 'date' => '2049-09-28', 'published' => false],

            3  => ['title' => 'Page 1', 'date' => '2049-09-29', 'published' => true],
            4  => ['title' => 'Page 2', 'date' => '2049-09-29', 'published' => true],

            5  => ['title' => 'Page 1', 'date' => '2049-09-30', 'published' => true],
            6  => ['title' => 'Page 2', 'date' => '2049-09-30', 'published' => true],
            7  => ['title' => 'Page 3', 'date' => '2049-09-30', 'published' => true],

            8  => ['title' => 'Page 1', 'date' => '2049-10-01', 'published' => true],
            9  => ['title' => 'Page 2', 'date' => '2049-10-01', 'published' => true],
            10 => ['title' => 'Page 3', 'date' => '2049-10-01', 'published' => true],

            11 => ['title' => 'Page 1', 'date' => '2049-10-02', 'published' => true],
            12 => ['title' => 'Page 2', 'date' => '2049-10-02', 'published' => true],
            13 => ['title' => 'Page 3', 'date' => '2049-10-02', 'published' => true],
            14 => ['title' => 'Page 4', 'date' => '2049-10-02', 'published' => true],
          ],
          'titles' => [
            1   => 'Page 1',
            2   => 'Page 2',

            3   => 'Page 1',
            4   => 'Page 2',

            5   => 'Page 1',
            6   => 'Page 2',
            7   => 'Page 3',

            8   => 'Page 1',
            9   => 'Page 2',
            10  => 'Page 3',

            11  => 'Page 1',
            12  => 'Page 2',
            13  => 'Page 3',
            14  => 'Page 4',
          ],
        ],
      ],
    ];

  }

  /**
   * Test tracking and then retrieving data tracked by the wiki node tracker.
   *
   * @dataProvider trackWikiNodeDataProvider
   */
  public function testTrackWikiNodes(array $nodeValues, array $expected): void {

    foreach ($nodeValues as $values) {

      /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
      $wikiNode = $this->drupalCreateWikiNode([
        'nid'     => $values['nid'],
        'title'   => $values['title'],
        'status'  => $values['status'],
      ], $values['date']);

      $this->wikiNodeTracker->trackWikiNode($wikiNode);

    }

    $this->assertEquals(
      $expected, $this->wikiNodeTracker->getTrackedWikiNodeData(),
    );

  }

}
