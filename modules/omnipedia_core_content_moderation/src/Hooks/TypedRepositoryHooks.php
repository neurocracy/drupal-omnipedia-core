<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core_content_moderation\Hooks;

use Drupal\hux\Attribute\Alter;
use Drupal\omnipedia_core_content_moderation\WrappedEntities\NodeWithModerationState;
use Drupal\omnipedia_core_content_moderation\WrappedEntities\WikiNodeWithModerationState;
use Drupal\typed_entity\ClassWithVariants;
use function array_push;
use function array_unshift;

/**
 * Typed entity repository hooks.
 */
class TypedRepositoryHooks {

  #[Alter('typed_repository_info')]
  /**
   * Adds our node entity wrapper definitions.
   *
   * @param array &$definitions
   *   Discovered typed repository definitions.
   */
  public function addModerationStateDefinitions(array &$definitions): void {

    /** @var string Fallback class name. */
    $fallback = $definitions['node']['wrappers']->fallback;

    /** @var string[] Variant class names. */
    $variants = $definitions['node']['wrappers']->variants;

    // Prepend this class so that it gets to try to match before the other
    // variants.
    array_unshift(
      $variants,
      WikiNodeWithModerationState::class,
    );

    // Push this class onto the end, so it's the last one before the fallback.
    //
    // @todo Should this be before the WikiNode class from the parent module?
    array_push(
      $variants,
      NodeWithModerationState::class,
    );

    $definitions['node']['wrappers'] = new ClassWithVariants(
      $fallback, $variants,
    );

  }

}
