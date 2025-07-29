<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\WrappedEntities;

/**
 * Interface for wrapped taxonomy term entities with wiki information.
 */
interface TaxonomyTermWithWikiInfoInterface {

  /**
   * Get the Discourse permalink slug this term provides, if any.
   *
   * @return string|null
   *   The Discourse permalink slug this term provides, or null if doesn't
   *   provide one.
   */
  public function getDiscoursePermalinkSlug(): ?string;

}
