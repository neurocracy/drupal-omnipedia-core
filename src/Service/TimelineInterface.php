<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\TranslatableMarkup;

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
  public function setCurrentDate(string|DrupalDateTime $date): void;

  /**
   * Validate and set the default date.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   Either a string that can be parsed by
   *   \Drupal\Core\Datetime\DrupalDateTime or an instance of said class.
   */
  public function setDefaultDate(string|DrupalDateTime $date): void;

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
   *   - 'first': Indicates that the first defined date is to be used.
   *
   *   - 'last': Indicates that the last defined date is to be used.
   *
   *   - A string that can be parsed by \Drupal\Core\Datetime\DrupalDateTime
   *     without errors.
   *
   *   - An instance of \Drupal\Core\Datetime\DrupalDateTime. This reduces
   *     redundant checks for whether you have a string or a DrupalDateTime
   *     object, as passing either into this method with normalize to a
   *     DrupalDateTime object.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. This is used
   *   if $date is 'first' or 'last'. Defaults to false.
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
  public function getDateObject(
    string|DrupalDateTime $date = 'current', bool $includeUnpublished = false
  ): DrupalDateTime;

  /**
   * Get a formatted date.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   Must be one of:
   *
   *   - 'current': Indicates the current date is to be used. This is the
   *     default.
   *
   *   - 'first': Indicates that a localized string representing the first
   *     available date should be returned.
   *
   *   - 'first': Indicates that a localized string representing the last
   *     available date should be returned.
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
   *   - 'html': The date format used when outputting to HTML, usually in a
   *     <time> element. This is defined by
   *     \Drupal\omnipedia_core\Service\Timeline::DATE_FORMAT_HTML.
   *
   *   - 'long': The long user-friendly date output format. This is defined by
   *     \Drupal\omnipedia_core\Service\Timeline::DATE_FORMAT_LONG. This is the
   *     default.
   *
   *   - 'short': The short user-friendly date output format. This is defined by
   *     \Drupal\omnipedia_core\Service\Timeline::DATE_FORMAT_SHORT.
   *
   *   Note that this parameter is ignored if $date is 'first' or 'last'.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The provided $date as a string, formatted according to $format, or
   *   localized text if $date is 'first' or 'last'.
   *
   * @see $this->getDateObject()
   *   $date is passed to this to ensure a date object is retrieved/created to
   *   format from.
   *
   * @todo Should the 'first' and 'last' options be moved to their own method or
   *   does it make more sense to have them here so that code that calls this
   *   doesn't have to care about whether they're passing a date or a keyword?
   *
   * @throws \InvalidArgumentException
   *   Exception thrown when the $format parameter isn't an expected value.
   */
  public function getDateFormatted(
    string|DrupalDateTime $date = 'current', string $format = 'long'
  ): string|TranslatableMarkup;

  /**
   * Determine if a given date is between/within a given range.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   The date or date keyword to test.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $startDate
   *   The start date or date keyword to use as the earliest date that $date can
   *   be.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $endDate
   *   The end date or date keyword to use as the latest date that $date can be.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. This is used
   *   if $startDate is 'first' or $endDate is 'last'. Defaults to false.
   *
   * @return boolean
   *   True if $date is after or the same as $startDate and that it is before or
   *   the same as $endDate, or false if those conditions are not met.
   *
   * @see $this->getDateObject()
   *   $date, $startDate, and $endDate are passed to this to parse and create
   *   date objects for the comparison.
   */
  public function isDateBetween(
    string|DrupalDateTime $date,
    string|DrupalDateTime $startDate,
    string|DrupalDateTime $endDate,
    bool $includeUnpublished = false
  ): bool;

  /**
   * Determine if two date ranges overlap.
   *
   * The two date ranges are considered to be overlapping if any of their days
   * occur on the same date. This means, for example, that if the end date for
   * one range and the start date for the other are on the same date, that will
   * be considered as overlapping; in that case, the start date should be moved
   * to next date to not be considered as overlapping.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $startDate1
   *   The date or date keyword to use as the start of the first date range.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $endDate1
   *   The date or date keyword to use as the end of the first date range.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $startDate2
   *   The date or date keyword to use as the start of the second date range.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $endDate2
   *   The date or date keyword to use as the end of the second date range.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content when
   *   resolving date keywords such as 'first' or 'last'. Defaults to false.
   *
   * @return bool
   *   True if there is an overlap between the two date ranges, or false if they
   *   don't overlap.
   */
  public function doDateRangesOverlap(
    string|DrupalDateTime $startDate1,
    string|DrupalDateTime $endDate1,
    string|DrupalDateTime $startDate2,
    string|DrupalDateTime $endDate2,
    bool $includeUnpublished = false
  ): bool;

  /**
   * Find all dates defined by content.
   *
   * Note that this method always rebuilds the lists of dates when invoked so it
   * should only be used when necessary, i.e. content has been updated.
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
