<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Drupal\omnipedia_core\Service\WikiInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The Omnipedia timeline service.
 */
class Timeline implements TimelineInterface {

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
   * The Drupal state key where we store the default date.
   */
  private const DEFAULT_DATE_STATE_KEY = 'omnipedia.default_date';

  /**
   * The Drupal state key where we store the list of dates defined by content.
   *
   * @see $this->findDefinedDates()
   *   Uses this constant to save dates to state storage.
   *
   * @see $this->getDefinedDates()
   *   Uses this constant to read dates from state storage.
   */
  private const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * The Drupal configuration object factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The Drupal database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * The Drupal entity type plug-in manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The Omnipedia wiki service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiInterface
   */
  private $wiki;

  /**
   * The Symfony session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  private $session;

  /**
   * The Drupal state system manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $stateManager;

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
   * The default date as a string.
   *
   * @var string
   */
  protected $defaultDateString;

  /**
   * The default date as a DrupalDateTime object.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  protected $defaultDateObject;

  /**
   * Dates defined by content.
   *
   * Two versions are stored, under the top level keys 'all' (published and
   * unpublished content) and 'published' (only published content). Each top
   * level key is an array of date strings in the 'storage' format.
   *
   * @var array
   *
   * @see $this->findDefinedDates()
   *   Scans content to build arrays of dates.
   *
   * @see $this->getDefinedDates()
   *   Use this to get these dates.
   *
   * @see self::DEFINED_DATES_STATE_KEY
   *   Drupal state key where dates are stored persistently between requests.
   */
  protected $definedDates;

  /**
   * Constructs this service object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The Drupal database connection service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
   *
   * @param \Drupal\omnipedia_core\Service\WikiInterface $wiki
   *   The Omnipedia wiki service.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The Symfony session service.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    ConfigFactoryInterface      $configFactory,
    Connection                  $database,
    EntityTypeManagerInterface  $entityTypeManager,
    WikiInterface               $wiki,
    SessionInterface            $session,
    StateInterface              $stateManager
  ) {
    // Save dependencies.
    $this->configFactory      = $configFactory;
    $this->database           = $database;
    $this->entityTypeManager  = $entityTypeManager;
    $this->wiki               = $wiki;
    $this->session            = $session;
    $this->stateManager       = $stateManager;
  }

  /**
   * Find and set the current date if it hasn't yet been set.
   *
   * @see $this->setCurrentDate()
   *   Validates and sets the current date.
   */
  protected function findCurrentDate(): void {
    // Don't do this twice.
    if (!empty($this->currentDateString)) {
      return;
    }

    // Retrieve the current date from session storage, if available, falling
    // back to the default date if not found.
    if ($this->session->has(self::CURRENT_DATE_SESSION_KEY)) {
      $date = $this->session->get(self::CURRENT_DATE_SESSION_KEY);

    } else {
      $this->findDefaultDate();

      $date = $this->defaultDateString;
    }

    $this->setCurrentDate($date);
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentDate($date): void {
    $dateObject = $this->getDateObject($date);

    $this->currentDateString = $this->getDateFormatted($dateObject, 'storage');

    // Save to session storage.
    $this->session->set(
      self::CURRENT_DATE_SESSION_KEY,
      $this->currentDateString
    );

    $this->currentDateObject = $dateObject;
  }

  /**
   * Find and set the default date if it hasn't yet been set.
   *
   * @see $this->setDefaultDate()
   *   Validates and sets the default date.
   *
   * @throws \UnexpectedValueException
   *   Exception thrown when the configured front page is not a node or a date
   *   cannot be retrieved from the front page node.
   */
  protected function findDefaultDate(): void {
    // Don't do this twice.
    if (!empty($this->defaultDateString)) {
      return;
    }

    /** @var string|null */
    $stateString = $this->stateManager->get(self::DEFAULT_DATE_STATE_KEY);

    // If we got a string instead of null, assume it's a date string, set it,
    // and return.
    if (is_string($stateString) && !empty($stateString)) {
      $this->setDefaultDate($stateString);

      return;
    }

    // If there's no default date set in the site state, we have to try to infer
    // it from the default front page.

    /** @var \Drupal\Core\Url */
    $urlObject = Url::fromUserInput(
      $this->configFactory->get('system.site')->get('page.front')
    );

    /** @var array */
    $routeParameters = $urlObject->getRouteParameters();

    if (empty($routeParameters['node'])) {
      throw new \UnexpectedValueException(
        'The front page does not appear to point to a node.'
      );
    }

    /** @var string|null */
    $nodeDate = $this->wiki->getWikiNodeDate($routeParameters['node']);

    if ($nodeDate === null) {
      throw new \UnexpectedValueException(
        'Could not read the default date from the front page node.'
      );
    }

    $this->setDefaultDate($nodeDate);
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultDate($date): void {
    $dateObject = $this->getDateObject($date);

    $this->defaultDateString = $this->getDateFormatted($dateObject, 'storage');

    // Save to state storage.
    $this->stateManager->set(
      self::DEFAULT_DATE_STATE_KEY,
      $this->defaultDateString
    );

    $this->defaultDateObject = $dateObject;
  }

  /**
   * {@inheritdoc}
   */
  public function getDateObject($date = 'current'): DrupalDateTime {
    if (is_string($date)) {
      if ($date === 'current') {
        $this->findCurrentDate();

        return $this->currentDateObject;

      } else if ($date === 'default') {
        $this->findDefaultDate();

        return $this->defaultDateObject;
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

      // Save the object to the cache so that we don't have to create it again.
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

  /**
   * {@inheritdoc}
   */
  public function findDefinedDates(): void {
    // This defines the keys used to store dates, while the values determine if
    // the key should include unpublished wiki nodes.
    /** @var array */
    $dateTypes = [
      'all'       => true,
      'published' => false
    ];

    /** @var array */
    $dates = [];

    /** @var string */
    $wikiNodeType = $this->wiki->getWikiNodeType();

    /** @var string */
    $wikiNodeFieldDateName = $this->wiki->getWikiNodeDateFieldName();

    /** @var \Drupal\Core\Entity\Query\QueryInterface */
    $nodeCountQuery = $this->entityTypeManager->getStorage('node')->getQuery();

    // This sets up the node query to limit to the wiki node content type and
    // marks it as a count query rather than returning the actual nodes.
    $nodeCountQuery
      ->condition('type', $wikiNodeType)
      ->count();

    // This selects the appropriate node field table column.
    /** @var \Drupal\Core\Database\Query\SelectInterface */
    $dateQuery = $this->database->select(
      'node__' . $wikiNodeFieldDateName, $wikiNodeFieldDateName . '_data');

    // This adds the field to the database query and returns the alias to refer
    // to it by - usually the third parameter, but a number may be appended.
    /** @var string */
    $dateFieldName = $dateQuery->addField(
      $wikiNodeFieldDateName . '_data', $wikiNodeFieldDateName . '_value',
      'date'
    );

    // This sets up the date field query to only return distinct values, and to
    // order and group by the date field.
    $dateQuery
      ->distinct()
      ->groupBy($dateFieldName)
      ->orderBy($dateFieldName);

    /** @var array */
    $dateResults = $dateQuery->execute()->fetchAll();

    foreach ($dateTypes as $dateType => $includeUnpublished) {
      // Make sure each date type has an array, to avoid errors if no results
      // are found.
      $dates[$dateType] = [];

      foreach ($dateResults as $resultItem) {
        // Create a clone of the node count query, so that we can run it
        // multiple times with different date field values without building it
        // from scratch each time.
        $localNodeCountQuery = (clone $nodeCountQuery)
          ->condition($wikiNodeFieldDateName, $resultItem->$dateFieldName);

        // Limit the query to published nodes if told to do so.
        if ($includeUnpublished === false) {
          $localNodeCountQuery->condition('status', 1);
        }

        $count = $localNodeCountQuery->execute();

        // If one or more nodes are found with this date, add the date to our
        // array of found dates.
        if ($count > 0) {
          $dates[$dateType][] = $resultItem->$dateFieldName;
        }
      }
    }

    // Save to state storage for retrieval in a future response.
    $this->stateManager->set(
      self::DEFINED_DATES_STATE_KEY,
      $dates
    );

    // Save to our property for quick retrieval within this request.
    $this->definedDates = $dates;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinedDates(bool $includeUnpublished = false): array {
    /** @var string */
    $dateTypeKey = $includeUnpublished ? 'all' : 'published';

    // If we've already saved the defined dates to the property, return that.
    if (isset($this->definedDates[$dateTypeKey])) {
      return $this->definedDates[$dateTypeKey];
    }

    // Attempt to load defined dates from state storage.
    /** @var array|null */
    $stateData = $this->stateManager->get(self::DEFINED_DATES_STATE_KEY);

    // If state storage returned an array instead of null, save it to the
    // property and return the appropriate data.
    if (is_array($stateData)) {
      $this->definedDates = $stateData;

      return $this->definedDates[$dateTypeKey];
    }

    // If neither the property nor the state data are set, scan content to find
    // and save the defined dates.
    $this->findDefinedDates();

    return $this->definedDates[$dateTypeKey];
  }

}
