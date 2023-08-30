<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Kernel;

use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\omnipedia_core\WrappedEntities\Node;
use Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface;
use Drupal\omnipedia_core\WrappedEntities\WikiNode;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\omnipedia_core\Kernel\WikiNodeKernelTestBase;
use Drupal\typed_entity\EntityWrapperInterface;

/**
 * Tests for the Omnipedia wiki node wrapped entity.
 *
 * @group omnipedia
 *
 * @group omnipedia_core
 */
class WikiNodeWrappedEntityTest extends WikiNodeKernelTestBase {

  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  /**
   * The Typed Entity repository manager.
   *
   * @var \Drupal\typed_entity\EntityWrapperInterface
   */
  protected readonly EntityWrapperInterface $typedEntityRepositoryManager;

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected readonly WikiNodeTrackerInterface $wikiNodeTracker;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->wikiNodeTracker = $this->container->get(
      'omnipedia.wiki_node_tracker'
    );

    $this->typedEntityRepositoryManager = $this->container->get(
      'Drupal\typed_entity\RepositoryManager',
    );

    $this->drupalCreateContentType(['type' => 'page']);

  }

  /**
   * Data provider for testContentTypes().
   *
   * @return array
   */
  public static function contentTypesProvider(): array {

    return [
      ['method' => 'drupalCreateWikiNode',  'arguments' => [
        [], '2049-09-28',
      ]],
      ['method' => 'drupalCreateNode',      'arguments' => [
        ['type' => 'page'],
      ]],
      ['method' => 'drupalCreateWikiNode',  'arguments' => [
        [], '2049-09-28',
      ]],
      ['method' => 'drupalCreateWikiNode',  'arguments' => [
        [], '2049-09-29',
      ]],
      ['method' => 'drupalCreateWikiNode',  'arguments' => [
        [], '2049-09-30',
      ]],
      ['method' => 'drupalCreateNode',      'arguments' => [
        ['type' => 'page'],
      ]],
    ];

  }

  /**
   * Test creating wiki node and non-wiki node wrapped entities.
   *
   * @dataProvider contentTypesProvider
   */
  public function testContentTypes(
    string $methodName, array $arguments,
  ): void {

    /** @var \Drupal\node\NodeInterface */
    $node = \call_user_func_array([$this, $methodName], $arguments);

    /** @var \Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface */
    $wrappedNode = $this->typedEntityRepositoryManager->wrap($node);

    $this->assertInstanceOf(NodeWithWikiInfoInterface::class, $wrappedNode);

    if ($methodName === 'drupalCreateWikiNode') {

      $this->assertInstanceOf(WikiNode::class, $wrappedNode);

      $this->assertTrue($wrappedNode->isWikiNode());

      $this->assertEquals($arguments[1], $wrappedNode->getWikiDate());

    } else {

      // The WikiNode class extends Node so explicitly assert that this isn't an
      // instance of WikiNode.
      $this->assertNotInstanceOf(WikiNode::class, $wrappedNode);

      $this->assertInstanceOf(Node::class, $wrappedNode);

      $this->assertFalse($wrappedNode->isWikiNode());

      $this->assertNull($wrappedNode->getWikiDate());

    }

  }

}
