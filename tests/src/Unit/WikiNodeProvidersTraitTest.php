<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Unit;

use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\Tests\omnipedia_core\Traits\WikiNodeProvidersTrait;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the Omnipedia wiki node providers trait.
 *
 * @group omnipedia
 *
 * @group omnipedia_core
 *
 * @coversDefaultClass \Drupal\Tests\omnipedia_core\Traits\WikiNodeProvidersTrait
 */
class WikiNodeProvidersTraitTest extends UnitTestCase {

  use WikiNodeProvidersTrait;

  /**
   * Tests the generateWikiDates() method return values.
   *
   * @covers ::generateWikiDates
   */
  public function testGenerateWikiNodeDates(): void {

    $dates = static::generateWikiDates();

    $this->assertIsArray($dates);

    $this->assertNotCount(0, $dates);

  }

  /**
   * Tests the generateWikiNodeCounts() method return values.
   *
   * @covers ::generateWikiNodeCounts
   */
  public function testGenerateWikiNodeCounts(): void {

    $counts = static::generateWikiNodeCounts(static::generateWikiDates());

    $this->assertIsArray($counts);

    $previousCount = 0;

    foreach ($counts as $date => $value) {

      // Assert that each day has more wiki nodes than the previous.
      $this->assertGreaterThan($previousCount, $value);

      $previousCount = $value;

    }

  }

  /**
   * Tests the generateWikiNodeTitles() method return values.
   *
   * @covers ::generateWikiNodeTitles
   */
  public function testGenerateWikiNodeTitles(): void {

    for ($i = 0; $i < 10; $i++) {

      $count = \rand(8, 15);

      $titles = static::generateWikiNodeTitles($count);

      $this->assertIsArray($titles);

      // Assert that the titles are all unique. This should always be the case
      // when using \Drupal\Component\Utility\Random so it may seem redundant
      // but this enforces this expectation from our trait method.
      $this->assertEquals($titles, \array_unique($titles));

    }

  }

  /**
   * Tests the generateWikiNodeValues() method return values.
   *
   * @covers ::generateWikiNodeValues
   */
  public function testGenerateWikiNodeValues(): void {

    $dates = static::generateWikiDates();

    $counts = static::generateWikiNodeCounts($dates);

    $titles = static::generateWikiNodeTitles(\end($counts));

    // Test valid combinations of parameters.
    foreach ([
      [],
      [$dates,  [],       []],
      [$dates,  $counts,  []],
      [$dates,  $counts,  $titles],
    ] as $values) {

      // Ensure that the default values work correctly when not provided.
      $this->assertNotCount(
        0, \call_user_func_array('static::generateWikiNodeValues', $values),
      );

    }

    // Test that invalid combinations of parameters throw the expected
    // exceptions.
    foreach ([
      [[],      $counts,  $titles],
      [[],      $counts,  []],
      [$dates,  [],       $titles],
      [[],      [],       $titles],
    ] as $values) {

      $this->expectException(\ArgumentCountError::class);

      \call_user_func_array('static::generateWikiNodeValues', $values);

    }

    $parameters = static::generateWikiNodeValues($dates, $counts, $titles);

    $this->assertNotCount(0, $parameters);

    foreach ($parameters as $i => $values) {

      $this->assertIsArray($values);

      $this->assertArrayHasKey('type', $values);

      // @todo Change this if/when we implement randomly adding other content
      //   types to the generated values. This currently acts to enforce the
      //   expectation that the wiki node type is always the only one.
      $this->assertEquals(WikiNodeInfo::TYPE, $values['type']);

      $this->assertArrayHasKey('title', $values);

      $this->assertArrayHasKey(WikiNodeInfo::DATE_FIELD, $values);

    }

  }

  /**
   * Tests the generateWikiNodeValues() method's $limit parameter.
   *
   * @covers ::generateWikiNodeValues
   */
  public function testGenerateWikiNodeValuesLimit(): void {

    $maxDates = count(static::generateWikiDates());

    foreach ([0, 1, 3, 5, 8, 10, 12, 20, 100] as $limit) {

      // The expected count. This ensures that whatever value we use is never
      // higher than the maximum number of dates available.
      $expectedCount = \min([$limit, $maxDates]);

      $parameters = static::generateWikiNodeValues(limit: $expectedCount);

      $this->assertCount($expectedCount, $parameters);

    }

  }

}
