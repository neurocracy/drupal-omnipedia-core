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
 */
class IsWikiMainPageCacheContext implements CalculatedCacheContextInterface {

  /**
   * The Omnipedia wiki node main page service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface
   */
  protected WikiNodeMainPageInterface $wikiNodeMainPage;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface $wikiNodeMainPage
   *   The Omnipedia wiki node main page service.
   */
  public function __construct(
    WikiNodeMainPageInterface $wikiNodeMainPage
  ) {
    $this->wikiNodeMainPage = $wikiNodeMainPage;
  }

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
