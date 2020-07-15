<?php

namespace Drupal\omnipedia_core\Entity;

use Drupal\node\NodeInterface as CoreNodeInterface;

/**
 * Omnipedia node entity interface.
 */
interface NodeInterface {

  /**
   * Get the wiki node type.
   *
   * This is a static method so that it can be used without needing to
   * instantiate a Node object.
   *
   * @return string
   *   The machine name of the wiki node type.
   */
  public static function getWikiNodeType(): string;

  /**
   * Determine if this node is a wiki node.
   *
   * @return boolean
   *   Returns true if the node is a wiki node or false otherwise.
   */
  public function isWikiNode(): bool;

  /**
   * Get the wiki node date field name.
   *
   * This is a static method so that it can be used without needing to
   * instantiate a Node object.
   *
   * @return string
   *   The machine name of the wiki node date field.
   */
  public static function getWikiNodeDateFieldName(): string;

  /**
   * Get the date field value from this wiki node.
   *
   * @return string|null
   *   Returns the string date of this node's date field if it is a wiki node
   *   or null otherwise.
   */
  public function getWikiNodeDate(): ?string;

}
