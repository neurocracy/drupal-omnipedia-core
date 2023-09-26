<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Base class for wiki node kernel tests.
 */
abstract class WikiNodeKernelTestBase extends KernelTestBase {

  use NodeCreationTrait {
    getNodeByTitle  as drupalGetNodeByTitle;
    createNode      as drupalCreateNode;
  }

  /**
   * {@inheritdoc}
   *
   * These are the minimum modules currently required to create wiki nodes.
   */
  protected static $modules = [
    'datetime', 'field', 'filter', 'menu_ui', 'node', 'omnipedia_core',
    'system', 'taxonomy', 'text', 'typed_entity', 'user',
  ];

  /**
   * {@inheritdoc}
   *
   * This installs the minimum required entity schemas and config to be able to
   * create wiki nodes.
   */
  protected function setUp(): void {

    parent::setUp();

    $this->installEntitySchema('filter_format');
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_vocabulary');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('user');

    $this->installConfig(['field', 'filter', 'node', 'omnipedia_core']);

  }

  /**
   * Create a wiki node given values and an optional wiki date.
   *
   * @param array $values
   *   Values to pass to NodeCreationTrait::createNode().
   *
   * @param string $date
   *   An optional wiki date.
   *
   * @return \Drupal\node\NodeInterface
   *
   * @see \Drupal\Tests\node\Traits\NodeCreationTrait::createNode()
   *   We wrap this, setting the node type and date field value.
   */
  protected function drupalCreateWikiNode(
    array $values, string $date = ''
  ): NodeInterface {

    if (!empty($date)) {
      $values[WikiNodeInfo::DATE_FIELD] = $date;
    }

    $values['type'] = WikiNodeInfo::TYPE;

    return $this->drupalCreateNode($values);

  }

}
