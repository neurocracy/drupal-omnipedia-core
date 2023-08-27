<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Entity;

/**
 * Defines constants for wiki nodes.
 */
final class WikiNodeInfo {

  /**
   * The wiki node content type.
   */
  public const TYPE = 'wiki_page';

  /**
   * The name of the date field on wiki nodes.
   */
  public const DATE_FIELD = 'field_date';

  /**
   * The name of the hide from search flag field on wiki nodes.
   */
  public const HIDDEN_FROM_SEARCH_FIELD = 'field_hide_from_search';

}
