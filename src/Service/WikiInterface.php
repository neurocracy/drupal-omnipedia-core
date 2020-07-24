<?php

namespace Drupal\omnipedia_core\Service;

/**
 * The Omnipedia wiki service interface.
 */
interface WikiInterface {

  /**
   * Get the wiki node type.
   *
   * @return string
   *   The machine name of the wiki node type.
   */
  public function getWikiNodeType(): string;

  /**
   * Get the wiki node date field name.
   *
   * @return string
   *   The machine name of the wiki node date field.
   */
  public function getWikiNodeDateFieldName(): string;

  /**
   * Get the date value from a provided wiki node, if possible.
   *
   * @param \Drupal\node\NodeInterface|int|string $node
   *   Either a node object or a numeric value (integer or string) that equates
   *   to an existing node ID to load.
   *
   * @return string|null
   *   Returns the string date of $node if it is a wiki node; returns null in
   *   all other cases.
   */
  public function getWikiNodeDate($node): ?string;

}
