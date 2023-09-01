<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Kernel;

use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\omnipedia_core\WrappedEntities\Node;
use Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface;
use Drupal\omnipedia_core\WrappedEntities\WikiNode;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\omnipedia_core\Kernel\WikiNodeKernelTestBase;
use Drupal\Tests\omnipedia_core\Traits\WikiNodeProvidersTrait;
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

  use WikiNodeProvidersTrait;

  /**
   * The Typed Entity repository manager.
   *
   * @var \Drupal\typed_entity\EntityWrapperInterface
   */
  protected readonly EntityWrapperInterface $typedEntityRepositoryManager;

  /**
   * The Omnipedia wiki node revision service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface
   */
  protected readonly WikiNodeRevisionInterface $wikiNodeRevision;

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

    $this->wikiNodeRevision = $this->container->get(
      'omnipedia.wiki_node_revision'
    );

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

  /**
   * Test the various revisions methods against their service counterparts.
   */
  public function testRevisions(): void {

    $parameters = static::generateWikiNodeValues();

    /** @var \Drupal\node\NodeInterface[] Node objects keyed by their nid. */
    $nodes = [];

    foreach ($parameters as $values) {

      /** @var \Drupal\node\NodeInterface */
      $node = $this->drupalCreateNode($values);
      // $node = \call_user_func_array([$this, 'drupalCreateNode'], $values);

      $this->wikiNodeTracker->trackWikiNode($node);

      $nodes[(int) $node->id()] = $node;

    }

    foreach ($nodes as $nid => $node) {

       /** @var \Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface */
      $wrappedNode = $this->typedEntityRepositoryManager->wrap($node);

      $revisions = $this->wikiNodeRevision->getWikiNodeRevisions($node);

      // Just in case.
      $this->assertNotCount(0, $revisions);

      $this->assertEquals($revisions, $wrappedNode->getWikiRevisions());

      // Test all of the revisions returned from the service against what the
      // wrapped entity provides us to assert they're equivalent.
      foreach ($revisions as $revisionNid => $revisionInfo) {

         /** @var \Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface */
        $revisionWrapped = $wrappedNode->getWikiRevision($revisionInfo['date']);

        $this->assertInstanceOf(
          NodeWithWikiInfoInterface::class, $revisionWrapped,
        );

        $this->assertEquals(
          $revisionInfo['nid'],
          $revisionWrapped->getEntity()->id(),
        );

        $this->assertEquals(
          $revisionInfo['date'],
          $revisionWrapped->getWikiDate(),
        );

      }

      /** @var \Drupal\node\NodeInterface|null */
      $previousService = $this->wikiNodeRevision->getPreviousRevision($node);

       /** @var \Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface|null */
      $previousWrapped = $wrappedNode->getPreviousWikiRevision();

      if (\is_object($previousService)) {

        $this->assertIsObject($previousWrapped);

        $this->assertInstanceOf(
          NodeWithWikiInfoInterface::class, $previousWrapped,
        );

        $this->assertEquals(
          $previousService->id(),
          $previousWrapped->getEntity()->id(),
        );

        $this->assertEquals(
          $previousService->get(WikiNodeInfo::DATE_FIELD)->getString(),
          $previousWrapped->getWikiDate(),
        );

      } else {

        $this->assertNull($previousService);

        $this->assertNull($previousWrapped);

      }

      $this->assertEquals(
        $this->wikiNodeRevision->hasPreviousRevision($node),
        $wrappedNode->hasPreviousWikiRevision(),
      );

    }

  }

}
