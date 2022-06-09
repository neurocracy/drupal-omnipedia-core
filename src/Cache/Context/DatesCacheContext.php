<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;

/**
 * Defines the Omnipedia date cache context service.
 *
 * Cache context ID: 'omnipedia_dates'.
 *
 * This allows for caching to vary based on the current Omnipedia date.
 */
class DatesCacheContext implements CalculatedCacheContextInterface {

  /**
   * The Omnipedia timeline service.
   *
   * @var \Drupal\omnipedia_core\Service\TimelineInterface
   */
  protected TimelineInterface $timeline;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   */
  public function __construct(TimelineInterface $timeline) {
    $this->timeline = $timeline;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return \t('Omnipedia date');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = null) {
    return $this->timeline->getDateFormatted('current', 'storage');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = null) {
    return new CacheableMetadata();
  }

}
