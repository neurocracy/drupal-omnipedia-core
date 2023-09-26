<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Cache\Context;

use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;

/**
 * Defines the Omnipedia is wiki main page cache context service.
 *
 * Cache context ID: 'omnipedia_is_wiki_main_page'.
 *
 * This allows for caching to vary on whether the current route is a wiki main
 * page.
 *
 * @deprecated in 5.x and is removed from 6.x. Use
 *   \Drupal\omnipedia_main_page\Cache\Context\IsWikiMainPageCacheContext
 *   instead.
 */
class IsWikiMainPageCacheContext implements CalculatedCacheContextInterface {

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface $wikiNodeMainPage
   *   The Omnipedia wiki node main page service.
   */
  public function __construct(
    protected readonly WikiNodeMainPageInterface $wikiNodeMainPage,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return \t('Omnipedia is wiki main page');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = null) {
    if ($this->wikiNodeMainPage->isCurrentRouteMainPage()) {
      return 'is_wiki_main_page';
    }

    return 'is_not_wiki_main_page';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = null) {
    return new CacheableMetadata();
  }
}
