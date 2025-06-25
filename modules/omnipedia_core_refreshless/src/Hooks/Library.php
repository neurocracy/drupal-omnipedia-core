<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core_refreshless\Hooks;

use Drupal\hux\Attribute\Alter;

/**
 * Library hook implementations.
 */
class Library {

  #[Alter('library_info')]
  /**
   * Alter library definitions to add our prefetch used notify to RefreshLess.
   */
  public function alterRefreshless(
    array &$libraries, string $extension,
  ): void {

    if ($extension !== 'refreshless_turbo') {
      return;
    }

    $libraries['refreshless'][
      'dependencies'
    ][] = 'omnipedia_core_refreshless/prefetch_used_notify';

  }

}
