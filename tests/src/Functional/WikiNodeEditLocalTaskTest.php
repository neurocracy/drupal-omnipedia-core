<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Functional;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\Node as WikiNode;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\RoleInterface;
use Drupal\user\RoleStorageInterface;

/**
 * Tests for wiki node 'Edit' local task visibility.
 *
 * @group omnipedia
 *
 * @group omnipedia_core
 */
class WikiNodeEditLocalTaskTest extends BrowserTestBase {

  /**
   * The Drupal configuration object factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected readonly ConfigFactoryInterface $configFactory;

  /**
   * The Drupal user role entity storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected readonly RoleStorageInterface $roleStorage;

  /**
   * The configured main page wiki node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected NodeInterface $mainPageNode;

  /**
   * The Omnipedia wiki node main page service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface
   */
  protected WikiNodeMainPageInterface $wikiNodeMainPage;

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected WikiNodeTrackerInterface $wikiNodeTracker;

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

    $this->configFactory = $this->container->get('config.factory');

    $this->wikiNodeMainPage = $this->container->get(
      'omnipedia.wiki_node_main_page'
    );

    $this->wikiNodeTracker = $this->container->get(
      'omnipedia.wiki_node_tracker'
    );

    $this->roleStorage = $this->container->get(
      'entity_type.manager'
    )->getStorage('user_role');

    /** @var \Drupal\node\NodeInterface */
    $this->mainPageNode = $this->drupalCreateNode([
      'title'       => $this->randomMachineName(8),
      'type'        => WikiNode::getWikiNodeType(),
      'status'      => NodeInterface::PUBLISHED,
      // A date is required for the wiki node tracker to function correctly.
      'field_date'  => '2049-10-01',
    ]);

    /** @var \Drupal\Core\Config\Config */
    $config = $this->configFactory->getEditable('system.site');

    $config->set(
      'page.front', $this->mainPageNode->toUrl()->toString()
    )->save();

    // Required so the main page service has data to pull in to correctly check
    // if the route is a main page.
    $this->wikiNodeTracker->trackWikiNode($this->mainPageNode);

    // Required to trigger the main page service to update the list of main
    // pages so that it matches when the 'Edit' local task event subscriber
    // checks if the current route is a main page.
    $this->wikiNodeMainPage->isMainPage($this->mainPageNode);

    $this->drupalPlaceBlock('local_tasks_block', [
      'region' => 'content', 'id' => self::LOCAL_TASKS_BLOCK_ID,
    ]);

  }

  /**
   * Assert that a local task with the provided Url is present on the page.
   *
   * @param \Drupal\Core\Url $url
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
  public function testEditLocalTaskVisibility(): void {

    /** @var \Drupal\node\NodeInterface */
    $node = $this->drupalCreateNode([
      'title'   => $this->randomMachineName(8),
      'type'    => WikiNode::getWikiNodeType(),
      'status'  => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($node->toUrl());

    $this->assertHasLocalTask($node->toUrl('edit-form'));

    /** @var \Drupal\user\RoleInterface */
    $anonymousRole = $this->roleStorage->load(RoleInterface::ANONYMOUS_ID);

    $anonymousRole->revokePermission('access content');

    $anonymousRole->trustData()->save();

    $this->drupalGet($node->toUrl());

    $this->assertNotHasLocalTask($node->toUrl('edit-form'));

  }

  /**
   * Test that node edit route shows access denied to users w/ 'access content'.
   */
  public function testEditRouteAccessDenied(): void {

    /** @var \Drupal\node\NodeInterface */
    $node = $this->drupalCreateNode([
      'title'   => $this->randomMachineName(8),
      'type'    => WikiNode::getWikiNodeType(),
      'status'  => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($node->toUrl('edit-form'));

    $this->assertSession()->statusCodeEquals(403);

    /** @var \Drupal\user\RoleInterface */
    $anonymousRole = $this->roleStorage->load(RoleInterface::ANONYMOUS_ID);

    $anonymousRole->revokePermission('access content');

    $anonymousRole->trustData()->save();

    $this->drupalGet($node->toUrl('edit-form'));

    $this->assertSession()->statusCodeEquals(404);

  }

  /**
   * Test that the 'Edit' local task only appear on main pages for real editors.
   *
   * I.e. it's hidden for users without real edit access but shown as expected
   * for users who actually have access to edit the page.
   */
  public function testMainPageNoEditLocalTask(): void {

    $this->drupalGet('');

    $this->assertNotHasLocalTask($this->mainPageNode->toUrl('edit-form'));

    $user = $this->drupalCreateUser([
      'access content',
      'edit any ' . WikiNode::getWikiNodeType() . ' content',
    ]);

    $this->drupalLogin($user);

    $this->drupalGet('');

    $this->assertHasLocalTask($this->mainPageNode->toUrl('edit-form'));

  }

}
