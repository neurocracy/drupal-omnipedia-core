<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * The Omnipedia timeline service interface.
 */
interface TimelineInterface {

  /**
   * Validate and set the current date.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   Either a string that can be parsed by
   *   \Drupal\Core\Datetime\DrupalDateTime or an instance of said class.
   */
  public function setCurrentDate($date): void;

  /**
   * Validate and set the default date.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   Either a string that can be parsed by
   *   \Drupal\Core\Datetime\DrupalDateTime or an instance of said class.
   */
  public function setDefaultDate($date): void;

  /**
   * Get a date object for a date.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   Must be one of:
   *
   *   - 'current': Indicates the current date is to be used. This is the
   *     default.
   *
   *   - 'default': Indicates the default date is to be used.
   *
   *   - A string that can be parsed by \Drupal\Core\Datetime\DrupalDateTime
   *     without errors.
   *
   *   - An instance of \Drupal\Core\Datetime\DrupalDateTime. This reduces
   *     redundant checks for whether you have a string or a DrupalDateTime
   *     object, as passing either into this method with normalize to a
   *     DrupalDateTime object.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   A date object representing $date. If $date was provided as a date object,
   *   it will be returned as-is.
   *
   * @throws \InvalidArgumentException
   *   Exception thrown when the there is an error in constructing a date object
   *   from $date, when the $date parameter is a date object but has errors, or
   *   when the $date parameter is neither a string nor an instance of the date
   *   object class.
   */
  public function getDateObject($date = 'current'): DrupalDateTime;

  /**
   * Get a formatted date.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   Must be one of:
   *
   *   - 'current': Indicates the current date is to be used. This is the
   *     default.
   *
   *   - 'default': Indicates the default date is to be used.
   *
   *   - A string that can be parsed by \Drupal\Core\Datetime\DrupalDateTime
   *     without errors.
   *
   *   - An instance of \Drupal\Core\Datetime\DrupalDateTime.
   *
   * @param string $format
   *   One of:
   *
   *   - 'storage': The date format stored in the database. This is defined by
   *     \Drupal\omnipedia_core\Service\Timeline::DATE_FORMAT_STORAGE.
   *
   *   - 'long': The long user-friendly date output format. This is defined by
   *     \Drupal\omnipedia_core\Service\Timeline::DATE_FORMAT_LONG. This is the
   *     default.
   *
   *   - 'short': The short user-friendly date output format. This is defined by
   *     \Drupal\omnipedia_core\Service\Timeline::DATE_FORMAT_SHORT.
   *
   * @return string
   *   The provided $date as a string, formatted according to $format.
   *
   * @see $this->getDateObject()
   *   $date is passed to this to ensure a date object is retrieved/created to
   *   format from.
   *
   * @throws \InvalidArgumentException
   *   Exception thrown when the $format parameter isn't an expected value.
   */
  public function getDateFormatted(
    $date = 'current', string $format = 'long'
  ): string;

  /**
   * Find all dates defined by content.
   *
   * Note that this method always performs a full scan of site content when
   * invoked so it should only be used when it's certain that content needs to
   * be rescanned.
   *
   * This is accomplished through a combination of a direct database query (to
   * find all distinct date values) and the entity query system (to find if any
   * wiki nodes exist with those values and are published/unpublished).
   *
   * Once the dates have been found and saved, they can be accessed via
   * $this->getDefinedDates().
   *
   * @see $this->getDefinedDates()
   *   Returns any defined dates.
   */
  public function findDefinedDates(): void;

  /**
   * Get a list of dates that have content.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. Defaults to
   *   false.
   *
   * @return array
   *   Zero or more unique dates that have content. Note that this will likely
   *   vary based on the $includeUnpublished parameter.
   */
  public function getDefinedDates(bool $includeUnpublished = false): array;

}
