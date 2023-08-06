<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Storage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeStorage as CoreNodeStorage;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;
use Drupal\omnipedia_core\Service\WikiNodeRevisionInterface;
use Drupal\omnipedia_core\Service\WikiNodeViewedInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Node storage handler for Omnipedia; injects dependencies into Node objects.
 *
 * @see \Drupal\node\NodeStorage
 *   Core node storage class this extends.
 *
 * @see \Drupal\Core\Entity\Sql\SqlContentEntityStorage
 *   Core entity storage class for SQL backends that the core node storage
 *   extends.
 *
 * @see https://www.drupal.org/project/developer_suite
 *   Partially inspired by this module that provides its own NodeStorage class
 *   to allow setting custom classes per node type or other entity bundles.
 */
class NodeStorage extends CoreNodeStorage {

  /**
   * Instantiates a new instance of this entity handler.
   *
   * This is a factory method that returns a new instance of this object. The
   * factory should pass any needed dependencies into the constructor of this
   * object, but not the container itself. Every call to this method must return
   * a new instance of this object; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this object should use.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   *
   * @return static
   *   A new instance of the entity handler.
   *
   * @see \Drupal\Core\Entity\EntityHandlerInterface::createInstance()
   *   Documentation copied from this; altered to use camel case for the second
   *   parameter.
   */
  public static function createInstance(
    ContainerInterface $container, EntityTypeInterface $entityType
  ) {
    return new static(
      $entityType,
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('omnipedia.wiki_node_main_page'),
      $container->get('omnipedia.wiki_node_revision'),
      $container->get('omnipedia.wiki_node_viewed')
    );
  }

  /**
   * Constructs this storage object; saves dependencies.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   *
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface|null $memoryCache
   *   The memory cache backend to be used.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface $wikiNodeMainPage
   *   The Omnipedia wiki node main page service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRevisionInterface $wikiNodeRevision
   *   The Omnipedia wiki node revision service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeViewedInterface $wikiNodeViewed
   *   The Omnipedia wiki node viewed service.
   *
   * @see \Drupal\Core\Entity\Sql\SqlContentEntityStorage::__construct()
   *   Documentation copied from this; altered to use camel case for parameters.
   */
  public function __construct(
    EntityTypeInterface           $entityType,
    Connection                    $database,
    EntityFieldManagerInterface   $entityFieldManager,
    CacheBackendInterface         $cache,
    LanguageManagerInterface      $languageManager,
    MemoryCacheInterface          $memoryCache,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    EntityTypeManagerInterface    $entityTypeManager,
    protected readonly WikiNodeMainPageInterface  $wikiNodeMainPage,
    protected readonly WikiNodeRevisionInterface  $wikiNodeRevision,
    protected readonly WikiNodeViewedInterface    $wikiNodeViewed,
  ) {

    parent::__construct(
      $entityType, $database, $entityFieldManager, $cache, $languageManager,
      $memoryCache, $entityTypeBundleInfo, $entityTypeManager
    );

  }

  /**
   * Maps from storage records to entity objects, and attaches fields.
   *
   * @param array $records
   *   Associative array of query results, keyed on the entity ID or revision
   *   ID.
   *
   * @param bool $loadFromRevision
   *   (optional) Flag to indicate whether revisions should be loaded or not.
   *   Defaults to false.
   *
   * @return array
   *   An array of entity objects implementing the EntityInterface.
   */
  protected function mapFromStorageRecords(
    array $records, $loadFromRevision = false
  ) {
    $entities = parent::mapFromStorageRecords($records, $loadFromRevision);

    // Inject dependencies for wiki nodes.
    foreach ($entities as $key => $node) {
      $node->injectWikiDependencies(
        $this->wikiNodeMainPage,
        $this->wikiNodeRevision,
        $this->wikiNodeViewed
      );
    }

    return $entities;
  }

}
