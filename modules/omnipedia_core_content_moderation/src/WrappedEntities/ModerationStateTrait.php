<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core_content_moderation\WrappedEntities;

/**
 * Trait for wrapped entities with moderation states.
 */
trait ModerationStateTrait {

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\content_moderation\ModerationInformationInterface::isDefaultRevisionPublished()
   *   We currently only care if there's a published default revision, so we use
   *   this method to determine that.
   */
  public function isPublished(): bool {

    return $this->moderationInfo->isDefaultRevisionPublished(
      $this->getEntity(),
    );

  }

}
