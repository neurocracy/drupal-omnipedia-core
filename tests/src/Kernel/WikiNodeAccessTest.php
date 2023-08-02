<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_core\Kernel;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Entity\Node as WikiNode;
use Drupal\omnipedia_core\Service\WikiNodeAccessInterface;
use Drupal\user\RoleInterface;
// use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests for the Omnipedia wiki node access service.
 */
class WikiNodeAccessTest extends KernelTestBase {

  use NodeCreationTrait {
    getNodeByTitle  as drupalGetNodeByTitle;
    createNode      as drupalCreateNode;
  }

  // use ContentTypeCreationTrait {
  //   createContentType as drupalCreateContentType;
  // }

  use UserCreationTrait {
    createUser      as drupalCreateUser;
    createRole      as drupalCreateRole;
    createAdminRole as drupalCreateAdminRole;
    setCurrentUser  as drupalSetCurrentUser;
  }

  /**
   * The Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected readonly EntityTypeManagerInterface $entityTypeManager;

  /**
   * The Omnipedia wiki node access service.
   *
   * @var Drupal\omnipedia_core\Service\WikiNodeAccessInterface
   */
  protected readonly WikiNodeAccessInterface $wikiNodeAccess;

  /**
   * {@inheritdoc}
   *
   * 'system' provides the 'access content' permission.
   */
  protected static $modules = ['node', 'omnipedia_core', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->installEntitySchema('user');

    $this->installConfig(['user']);

    $this->installEntitySchema('node');

    $this->installSchema('node', 'node_access');

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->wikiNodeAccess = $this->container->get(
      'omnipedia.wiki_node_access'
    );

  }

  /**
   * Test the canUserAccessAnyWikiNode() method.
   */
  public function testCanUserAccessAnyWikiNode(): void {

    $this->assertEquals(
      false, $this->wikiNodeAccess->canUserAccessAnyWikiNode(null),
    );

    /** @var \Drupal\node\NodeStorageInterface */
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    /** @var \Drupal\user\RoleInterface */
    $authenticatedRole = $this->entityTypeManager->getStorage(
      'user_role',
    )->load(RoleInterface::AUTHENTICATED_ID);

    /** @var \Drupal\node\NodeInterface */
    // $wikiNode = $nodeStorage->create([
    //   'title'       => $this->randomMachineName(8),
    //   'type'        => WikiNode::getWikiNodeType(),
    //   'status'      => NodeInterface::PUBLISHED,
    // ]);

    // $nodeStorage->save($wikiNode);

    $testUser = $this->drupalCreateUser();

    // $this->drupalSetCurrentUser($testUser);

    // $this->assertEquals(
    //   false, $this->wikiNodeAccess->canUserAccessAnyWikiNode($testUser),
    // );

    /** @var \Drupal\node\NodeInterface */
    $wikiNode = $this->drupalCreateNode([
      'title'       => $this->randomMachineName(8),
      'type'        => WikiNode::getWikiNodeType(),
      'status'      => NodeInterface::PUBLISHED,
      // A date is required for the wiki node tracker to function correctly.
      // 'field_date'  => '2049-10-01',
    ]);

    // $testUser = $this->drupalCreateUser(['access content']);
    // $testUser = $this->drupalCreateUser();

    $this->assertEquals(
      true, $this->wikiNodeAccess->canUserAccessAnyWikiNode($testUser),
    );

    $wikiNode->setUnpublished()->save();

    // // $nodeStorage->save($wikiNode);

    // $nodeStorage->delete([$wikiNode]);

    // $this->assertEquals(
    //   0, ($nodeStorage->getQuery())
    //   ->condition('type', WikiNode::getWikiNodeType())
    //   ->accessCheck(true)
    //   ->addMetaData('account', $testUser)
    //   ->addTag('node_access')
    //   ->count()->execute()
    // );

    // $authenticatedRole->revokePermission('access content')->trustData()->save();

    $this->assertEquals(
      false, $this->wikiNodeAccess->canUserAccessAnyWikiNode($testUser),
    );

  }

}
