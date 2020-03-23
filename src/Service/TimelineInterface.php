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
   */
  public function getDateFormatted(
    $date = 'current', string $format = 'long'
  ): string;
}
