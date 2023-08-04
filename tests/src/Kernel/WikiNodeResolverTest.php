<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Kernel;

use Drupal\omnipedia_core\Entity\NodeInterface as WikiNodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\omnipedia_core\Kernel\WikiNodeKernelTestBase;

/**
 * Tests for the Omnipedia wiki node resolver service.
 *
 * @group omnipedia
 *
 * @group omnipedia_core
 */
class WikiNodeResolverTest extends WikiNodeKernelTestBase {

  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  /**
   * The Omnipedia wiki node resolver service.
   *
   * @var Drupal\omnipedia_core\Service\WikiNodeResolverInterface
   */
  protected readonly WikiNodeResolverInterface $wikiNodeResolver;

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected readonly WikiNodeTrackerInterface $wikiNodeTracker;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->wikiNodeResolver = $this->container->get(
      'omnipedia.wiki_node_resolver'
    );

    $this->wikiNodeTracker = $this->container->get(
      'omnipedia.wiki_node_tracker'
    );

    $this->drupalCreateContentType(['type' => 'page']);

  }

  /**
   * Data provider for testResolveNodeValid() / testResolveWikiNodeValid().
   *
   * Note that it doesn't seem possible to create nodes in a data provider
   * method because PHPUnit seems to invoke this before the Drupal services
   * container has been initialized. While we could initialize the container
   * ourselves here, that feels unnecessary and could cause unexpected problems,
   * so instead we return the method names and arguments needed to create the
   * test nodes.
   *
   * @return array
   */
  public function resolveNodeValidProvider(): array {

    return [
      ['method' => 'drupalCreateWikiNode',  'arguments' => [
        [], '2049-09-28',
      ]],
      ['method' => 'drupalCreateNode',      'arguments' => [
        ['type' => 'page'],
      ]],
    ];

  }

  /**
   * Test the resolveNode() method with valid values.
   *
   * @dataProvider resolveNodeValidProvider
   */
  public function testResolveNodeValid(
    string $methodName, array $arguments,
  ): void {

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
    $node = \call_user_func_array([$this, $methodName], $arguments);

    $this->assertSame(
      $node, $this->wikiNodeResolver->resolveNode($node),
    );

    $this->assertEquals(
      $node->nid->getString(),
      $this->wikiNodeResolver->resolveNode($node)->nid->getString(),
    );

    $this->assertEquals(
      (int) $node->nid->getString(),
      (int) $this->wikiNodeResolver->resolveNode($node)->nid->getString(),
    );

  }

  /**
   * Data provider for testResolveNodeInvalid() / testResolveWikiNodeInvalid().
   *
   * This returns various data types that are technically valid types for
   * WikiNodeResolverInterface::resolveNode() but that cannot be resolved to an
   * existing node of any content type.
   *
   * @return array
   */
  public function resolveNodeInvalidProvider(): array {

    return [
      [new \stdClass()],
      [123],
      ['456'],
      ['baby-shark-do-do-do-do'],
    ];

  }

  /**
   * Test the resolveNode() method with invalid values.
   *
   * @dataProvider resolveNodeInvalidProvider
   */
  public function testResolveNodeInvalid(mixed $data): void {

    $this->assertNull(
      $this->wikiNodeResolver->resolveNode($data),
    );

  }

  /**
   * Test the resolveWikiNode() method with valid values.
   *
   * @dataProvider resolveNodeValidProvider
   */
  public function testResolveWikiNodeValid(
    string $methodName, array $arguments,
  ): void {

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface */
    $node = \call_user_func_array([$this, $methodName], $arguments);

    // Split asserts based on whether this is a wiki node or a different content
    // type, as the latter is expected to always return null from this method.
    if ($methodName === 'drupalCreateWikiNode') {

      $this->assertSame(
        $node, $this->wikiNodeResolver->resolveWikiNode($node),
      );

      $this->assertEquals(
        $node->nid->getString(),
        $this->wikiNodeResolver->resolveWikiNode($node)->nid->getString(),
      );

      $this->assertEquals(
        (int) $node->nid->getString(),
        (int) $this->wikiNodeResolver->resolveWikiNode($node)->nid->getString(),
      );

    } else {

      $this->assertNull($this->wikiNodeResolver->resolveWikiNode($node));

      $this->assertNull($this->wikiNodeResolver->resolveWikiNode(
        $node->nid->getString(),
      ));

      $this->assertNull($this->wikiNodeResolver->resolveWikiNode(
        (int) $node->nid->getString(),
      ));

    }

  }

  /**
   * Test the resolveWikiNode() method with invalid values.
   *
   * @dataProvider resolveNodeInvalidProvider
   */
  public function testResolveWikiNodeInvalid(mixed $data): void {

    $this->assertNull(
      $this->wikiNodeResolver->resolveWikiNode($data),
    );

  }

}
