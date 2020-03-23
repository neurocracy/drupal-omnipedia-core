<?php

namespace Drupal\omnipedia_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
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
   * The Drupal configuration object factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The Drupal entity type plug-in manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

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
   * Constructs this service object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The Symfony session service.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    ConfigFactoryInterface      $configFactory,
    EntityTypeManagerInterface  $entityTypeManager,
    SessionInterface            $session,
    StateInterface              $stateManager
  ) {
    // Save dependencies.
    $this->configFactory      = $configFactory;
    $this->entityTypeManager  = $entityTypeManager;
    $this->session            = $session;
    $this->stateManager       = $stateManager;

    $this->findCurrentDate();
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

    /** @var \Drupal\node\NodeInterface */
    $node = $this->entityTypeManager->getStorage('node')->load(
      $urlObject->getRouteParameters()['node']
    );

    $this->setDefaultDate($node->get('field_date')[0]->value);
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
