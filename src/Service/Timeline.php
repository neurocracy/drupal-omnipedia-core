<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The Omnipedia timeline service.
 */
class Timeline implements TimelineInterface {
  /**
   * The default date when one can't be determined from content or session.
   *
   * @todo Instead of hard-coding here, grab the date field from the default
   * front page node and store it in the Drupal state data to be retrieved in
   * $this->findCurrentDate() if found.
   */
  public const DATE_DEFAULT = '2049-09-29';

  /**
   * The date format stored in the database.
   *
   * @see \Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface::DATE_STORAGE_FORMAT
   *   An alias for this Drupal core constant.
   */
  public const DATE_FORMAT_STORAGE = DateTimeItemInterface::DATE_STORAGE_FORMAT;

  /**
   * The long user-friendly date output format.
   *
   * @see https://www.php.net/manual/en/function.date
   *   Format reference.
   */
  public const DATE_FORMAT_LONG = 'F jS Y';

  /**
   * The short user-friendly date output format.
   *
   * @see https://www.php.net/manual/en/function.date
   *   Format reference.
   */
  public const DATE_FORMAT_SHORT = 'Y/m/d';

  /**
   * The Symfony session attribute key where we store the current date.
   *
   * @see https://symfony.com/doc/3.4/components/http_foundation/sessions.html#namespaced-attributes
   */
  private const CURRENT_DATE_SESSION_KEY = 'omnipedia/currentDate';

  /**
   * The Symfony session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  private $session;

  /**
   * A cache of created date objects.
   *
   * These are keyed by their date string representation in 'storage' format.
   *
   * @var array
   */
  protected $dateObjectCache = [];

  /**
   * The current date as a string.
   *
   * @var string
   */
  protected $currentDateString;

  /**
   * The current date as a DrupalDateTime object.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  protected $currentDateObject;

  /**
   * Constructs this service object.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The Symfony session service.
   */
  public function __construct(SessionInterface $session) {
    // Save dependencies.
    $this->session = $session;

    $this->findCurrentDate();
  }

  /**
   * Find and set the current date if it hasn't yet been set.
   *
   * @see $this->setCurrentDate()
   *   Validates and sets the current date.
   *
   * @todo Can this just be merged into $this->__construct()?
   */
  protected function findCurrentDate(): void {
    // Don't do this twice.
    if (!empty($this->currentDateString)) {
      return;
    }

    // Retrieve the current date from session storage, if available, falling
    // back to the default date if not found.
    //
    // @todo When self::DATE_DEFAULT is removed, use $this->session->has() to
    // check if the current date exists in session data, so that we don't do any
    // extra work to get the default date if we don't need to.
    $date = $this->session->get(
      self::CURRENT_DATE_SESSION_KEY,
      self::DATE_DEFAULT
    );

    $this->setCurrentDate($date);
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentDate($date): void {
    $dateObject = $this->getDateObject($date);

    $this->currentDateString = $dateObject->format(self::DATE_FORMAT_STORAGE);

    // Save to session storage.
    $this->session->set(
      self::CURRENT_DATE_SESSION_KEY,
      $this->currentDateString
    );

    $this->currentDateObject = $dateObject;
  }

  /**
   * {@inheritdoc}
   */
  public function getDateObject($date = 'current'): DrupalDateTime {
    if (is_string($date)) {
      if ($date === 'current') {
        return $this->currentDateObject;
      }

      // If a valid and error-free date object already exists in the cache for
      // this $date string, return it.
      if (isset($this->dateObjectCache[$date])) {
        return $this->dateObjectCache[$date];
      }

      $dateObject = DrupalDateTime::createFromFormat(
        self::DATE_FORMAT_STORAGE,
        $date
      );

      if ($dateObject->hasErrors()) {
        throw new \InvalidArgumentException(
          'There were one or more errors in constructing a \Drupal\Core\Datetime\DrupalDateTime object:' .
          "\n" . implode("\n", $dateObject->getErrors())
        );
      }

      // Save the object to the cache so that we don't have create it again.
      $this->dateObjectCache[$date] = $dateObject;

      return $dateObject;

    } else if ($date instanceof DrupalDateTime) {
      if ($date->hasErrors()) {
        throw new \InvalidArgumentException(
          'There are one or more errors with the provided \Drupal\Core\Datetime\DrupalDateTime object:' .
          "\n" . implode("\n", $date->getErrors())
        );
      }

      return $date;

    } else {
      throw new \InvalidArgumentException('The $date parameter must either be a string or an instance of \Drupal\Core\Datetime\DrupalDateTime.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDateFormatted(
    $date = 'current', string $format = 'long'
  ): string {
    switch ($format) {
      case 'storage':
        $formatString = self::DATE_FORMAT_STORAGE;

        break;

      case 'long':
        $formatString = self::DATE_FORMAT_LONG;

        break;

      case 'short':
        $formatString = self::DATE_FORMAT_SHORT;

        break;

      default:
        throw new \InvalidArgumentException('The $format parameter must one of "storage", "long", or "short".');
    }

    return $this->getDateObject($date)->format($formatString);
  }
}
