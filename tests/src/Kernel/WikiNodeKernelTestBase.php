<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;
use Drupal\omnipedia_core\Entity\Node as WikiNode;
use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\omnipedia_core\Storage\NodeStorage as WikiNodeStorage;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Base class for wiki node kernel tests.
 */
abstract class WikiNodeKernelTestBase extends KernelTestBase {

  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

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
   * create wiki nodes, and sets our wiki node storage as the node storage along
   * with our extend node entity class since our entity type build hook is not
   * called here.
   */
  protected function setUp(): void {

    parent::setUp();

    $this->installEntitySchema('filter_format');
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_vocabulary');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('user');

    $this->installConfig(['field', 'filter', 'node', 'omnipedia_core']);

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface The Drupal entity type manager. */
    $entityTypeManager = $this->container->get('entity_type.manager');

    // These two are necessary for our custom node entity and storage classes to
    // be used by Drupal. This would normally be handled by Drupal when
    // invoking the entity type build hook, but that hook isn't invoked during
    // kernel tests.
    $entityTypeManager->clearCachedDefinitions();
    $this->container->get('entity.memory_cache')->reset();

    $entityTypeManager->getDefinition('node')
      ->setClass(WikiNode::class)
      ->setStorageClass(WikiNodeStorage::class);

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
   * @return \Drupal\omnipedia_core\Entity\NodeInterface
   *
   * @see \Drupal\Tests\node\Traits\NodeCreationTrait::createNode()
   *   We wrap this, setting the node type and date field value.
   */
  protected function drupalCreateWikiNode(
    array $values, string $date = ''
  ): WikiNodeInterface {

    if (!empty($date)) {
      $values[WikiNodeInfo::DATE_FIELD] = $date;
    }

    $values['type'] = WikiNodeInfo::TYPE;

    return $this->drupalCreateNode($values);

  }

}
