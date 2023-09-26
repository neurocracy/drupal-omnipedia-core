<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Functional;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\RoleInterface;

/**
 * Tests for wiki node 'Edit' local task visibility.
 *
 * @group omnipedia
 *
 * @group omnipedia_core
 */
class WikiNodeEditLocalTaskTest extends BrowserTestBase {

  /**
   * The Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected readonly EntityTypeManagerInterface $entityTypeManager;

  /**
   * The local tasks HTML 'id' attribute slug.
   */
  protected const LOCAL_TASKS_BLOCK_ID = 'local-tasks-block';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block', 'field', 'node', 'omnipedia_access', 'omnipedia_core', 'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->drupalPlaceBlock('local_tasks_block', [
      'region' => 'content', 'id' => self::LOCAL_TASKS_BLOCK_ID,
    ]);

  }

  /**
   * Assert that a local task with the provided Url is present on the page.
   *
   * @param \Drupal\Core\Url $url
   *
   * @todo Rework this into a reusable trait.
   */
  protected function assertHasLocalTask(Url $url): void {

    // @see \Drupal\Core\Utility\LinkGenerator::generate()
    //   'data-drupal-link-system-path' attributes are generated here using
    //   Url::getInternalPath() so we use the same method to build our selector.
    $this->assertSession()->elementExists('css',
      '#block-' . self::LOCAL_TASKS_BLOCK_ID . ' ' .
      'a[data-drupal-link-system-path="' . $url->getInternalPath() . '"]'
    );

  }

  /**
   * Assert that a local task with the provided Url is not present on the page.
   *
   * @param \Drupal\Core\Url $url
   *
   * @todo Rework this into a reusable trait.
   */
  protected function assertNotHasLocalTask(Url $url): void {

    // @see \Drupal\Core\Utility\LinkGenerator::generate()
    //   'data-drupal-link-system-path' attributes are generated here using
    //   Url::getInternalPath() so we use the same method to build our selector.
    $this->assertSession()->elementNotExists('css',
      '#block-' . self::LOCAL_TASKS_BLOCK_ID . ' ' .
      'a[data-drupal-link-system-path="' . $url->getInternalPath() . '"]'
    );

  }

  /**
   * Test that the 'Edit' local task is visible to users with 'access content'.
   */
  public function testWikiNodeEditLocalTaskVisibility(): void {

    /** @var \Drupal\node\NodeInterface */
    $node = $this->drupalCreateNode([
      'title'   => $this->randomMachineName(8),
      'type'    => WikiNodeInfo::TYPE,
      'status'  => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($node->toUrl());

    $this->assertHasLocalTask($node->toUrl('edit-form'));

    /** @var \Drupal\user\RoleInterface */
    $anonymousRole = $this->entityTypeManager->getStorage('user_role')->load(
      RoleInterface::ANONYMOUS_ID,
    );

    $anonymousRole->revokePermission('access content');

    $anonymousRole->trustData()->save();

    $this->drupalGet($node->toUrl());

    $this->assertNotHasLocalTask($node->toUrl('edit-form'));

  }

  /**
   * Test that node edit route shows access denied to users w/ 'access content'.
   */
  public function testWikiNodeEditRouteAccessDenied(): void {

    /** @var \Drupal\node\NodeInterface */
    $node = $this->drupalCreateNode([
      'title'   => $this->randomMachineName(8),
      'type'    => WikiNodeInfo::TYPE,
      'status'  => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($node->toUrl('edit-form'));

    $this->assertSession()->statusCodeEquals(403);

    /** @var \Drupal\user\RoleInterface */
    $anonymousRole = $this->entityTypeManager->getStorage('user_role')->load(
      RoleInterface::ANONYMOUS_ID,
    );

    $anonymousRole->revokePermission('access content');

    $anonymousRole->trustData()->save();

    $this->drupalGet($node->toUrl('edit-form'));

    $this->assertSession()->statusCodeEquals(404);

  }

  /**
   * Test that non-wiki nodes don't show their 'Edit' local task.
   *
   * This ensures that the 'Edit' local task visibility changes we perform are
   * only applied to wiki nodes and not other content types.
   */
  public function testNonWikiNodeLocalTaskVisibility(): void {

    $this->drupalCreateContentType(['type' => 'page']);

    /** @var \Drupal\node\NodeInterface */
    $node = $this->drupalCreateNode([
      'title'   => $this->randomMachineName(8),
      'type'    => 'page',
      'status'  => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($node->toUrl());

    $this->assertNotHasLocalTask($node->toUrl('edit-form'));

  }

  /**
   * Test that non-wiki node edit route shows not found.
   *
   * This ensures that we only expose 403s on wiki node edit routes and not
   * other content types.
   */
  public function testNonWikiNodeEditRouteNotFound(): void {

    $this->drupalCreateContentType(['type' => 'page']);

    /** @var \Drupal\node\NodeInterface */
    $node = $this->drupalCreateNode([
      'title'   => $this->randomMachineName(8),
      'type'    => 'page',
      'status'  => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($node->toUrl('edit-form'));

    $this->assertSession()->statusCodeEquals(404);

  }

}
