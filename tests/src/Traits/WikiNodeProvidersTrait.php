<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Traits;

use Drupal\Component\Utility\Random;
use Drupal\omnipedia_core\Entity\WikiNodeInfo;

/**
 * Test trait for wiki node data providers.
 */
trait WikiNodeProvidersTrait {

  /**
   * Generate a series of wiki dates.
   *
   * @return string[]
   *
   * @todo Add parameter to randomly drop days to create gaps?
   *
   * @todo Add options to create longer or shorter date ranges?
   */
  protected static function generateWikiDates(): array {

    return [
      '2049-09-28',
      '2049-09-29',
      '2049-09-30',
      '2049-10-01',
      '2049-10-02',
      '2049-10-03',
      '2049-10-04',
      '2049-10-05',
      '2049-10-06',
      '2049-10-07',
      '2049-10-08',
      '2049-10-09',
      '2049-10-10',
    ];

  }

  /**
   * Generate wiki node counts for each provided date, increasing each day.
   *
   * @param string[] $dates
   *   String dates to use as keys.
   *
   * @return int[]
   *   Array of integer node counts, keyed by the values of $dates, and
   *   increasing by one to three each date.
   */
  public static function generateWikiNodeCounts(array $dates): array {

    $wikiNodesPerDate = [];

    $count = 0;

    foreach ($dates as $date) {

      $count += \rand(1, 3);

      $wikiNodesPerDate[$date] = $count;

    }

    return $wikiNodesPerDate;

  }

  /**
   * Generate random unique wiki node titles.
   *
   * @param int $count
   *   The number of unique titles to generate.
   *
   * @param int $length
   *   The length of the generated title strings. Defaults to 8.
   *
   * @return string[]
   *
   * @see \Drupal\Component\Utility\Random::name()
   *   Used to ensure randomly generated names are not duplicated.
   */
  public static function generateWikiNodeTitles(
    int $count, int $length = 8,
  ): array {

    $titles = [];

    /** @var \Drupal\Component\Utility\Random */
    $randomizer = new Random();

    for ($i=0; $i < $count; $i++) {
      $titles[] = $randomizer->name($length);
    }

    return $titles;

  }

  /**
   * Generate arguments arrays suitable for NodeCreationTrait::createNode().
   *
   * @param string[] $dates
   *   Array of dates to generate values for. Optional if none of the other
   *   parameters are provided, but required if any of the others are provided.
   *
   * @param int[] $counts
   *   Counts per day, keyed by the values in $dates.
   *
   * @param string[] $titles
   *   Array of unique string titles.
   *
   * @return array
   *
   * @throws \ArgumentCountError
   *   If $counts is provided but $dates is not, or if $titles is provided but
   *   neither $dates nor $counts are provided.
   *
   * @see \Drupal\Tests\node\Traits\NodeCreationTrait::createNode()
   *
   * @todo Add a parameter to randomly add other node types?
   */
  public static function generateWikiNodeValues(
    array $dates  = [],
    array $counts = [],
    array $titles = [],
  ): array {

    if (count($dates) === 0 && count($counts) > 0) {

      throw new \ArgumentCountError(
        'The $dates parameter must be provided if $counts is provided!',
      );

    }

    if (count($dates) === 0 && count($counts) === 0 && count($titles) > 0) {

      throw new \ArgumentCountError(
        'The $dates parameter must be provided if $counts is omitted and $titles is provided!',
      );

    }

    if (count($dates) === 0) {
      $dates = static::generateWikiDates();
    }

    if (count($counts) === 0) {
      $counts = static::generateWikiNodeCounts($dates);
    }

    if (count($titles) === 0) {
      $titles = static::generateWikiNodeTitles(\end($counts));
    }

    $parameters = [];

    foreach ($counts as $date => $count) {

      for ($i = 0; $i < $count; $i++) {
        $parameters[] = [
          'type'  => WikiNodeInfo::TYPE,
          'title' => $titles[$i],
          WikiNodeInfo::DATE_FIELD => $date,
        ];
      }

    }

    return $parameters;

  }

}
