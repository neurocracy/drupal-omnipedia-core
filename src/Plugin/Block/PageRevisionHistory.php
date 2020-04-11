<?php

namespace Drupal\omnipedia_core\Plugin\Block;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\Core\Url;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Drupal\omnipedia_core\Service\WikiInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Page revision history block.
 *
 * This displays a list of all available revisions of a page, with links to view
 * each revision.
 *
 * @Block(
 *   id           = "omnipedia_page_revision_history",
 *   admin_label  = @Translation("Page revision history"),
 *   category     = @Translation("Omnipedia"),
 * )
 */
class PageRevisionHistory extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The Drupal access manager service.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * The Drupal current route match service.
   *
   * @var \Drupal\Core\Routing\StackedRouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The Omnipedia timeline service.
   *
   * @var \Drupal\omnipedia_core\Service\TimelineInterface
   */
  protected $timeline;

  /**
   * The Omnipedia wiki service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiInterface
   */
  protected $wiki;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Access\AccessManagerInterface $accessManager
   *   The Drupal access manager service.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_core\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiInterface $wiki
   *   The Omnipedia wiki service.
   */
  public function __construct(
    array $configuration, string $pluginID, array $pluginDefinition,
    AccessManagerInterface      $accessManager,
    StackedRouteMatchInterface  $currentRouteMatch,
    TimelineInterface           $timeline,
    WikiInterface               $wiki
  ) {
    parent::__construct($configuration, $pluginID, $pluginDefinition);

    // Save dependencies.
    $this->accessManager      = $accessManager;
    $this->currentRouteMatch  = $currentRouteMatch;
    $this->timeline           = $timeline;
    $this->wiki               = $wiki;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration, $pluginID, $pluginDefinition
  ) {
    return new static(
      $configuration, $pluginID, $pluginDefinition,
      $container->get('access_manager'),
      $container->get('current_route_match'),
      $container->get('omnipedia.timeline'),
      $container->get('omnipedia.wiki')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // If a label has been set by the user, defer to that.
    if (!empty($this->configuration['label'])) {
      return $this->configuration['label'];
    }

    // Otherwise we use this.
    return $this->t('Revision history');
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineNameSuggestion() {
    return 'page_revision_history';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->currentRouteMatch->getParameter('node');

    // This contains the render array for the block.
    /** @var array */
    $renderArray = [
      '#cache' => [
        'contexts'  => ['omnipedia_wiki_node', 'user.permissions'],
        'max-age'   => Cache::PERMANENT,
      ],
    ];

    // Return the render array with just cache metadata if the current route
    // doesn't contain a wiki node as a parameter.
    if (!$this->wiki->isWikiNode($node)) {
      return $renderArray;
    }

    // The base class for the revision list.
    /** @var string */
    $listClass = 'omnipedia-wiki-page-revisions';

    $renderArray['revision_list'] = [
      '#theme'      => 'item_list',
      '#list_type'  => 'ol',
      '#items'      => [],
      '#attributes' => [
        'class'       => [$listClass],
      ],
    ];

    // Data for this wiki node and its revisions.
    /** @var array */
    $nodeRevisions = $this->wiki->getWikiNodeRevisions($node);

    foreach ($nodeRevisions as $nodeRevision) {
      // The route to check access to and link to.
      /** @var string */
      $routeName = 'entity.node.canonical';

      // The route parameters to check access to and link to.
      /** @var array */
      $routeParameters = ['node' => $nodeRevision['nid']];

      // Add a cache tag for this node so that this is invalidated if/when the
      // node changes. Note that this still needs to be added even if the user
      // does not have access to this for when/if access is granted so that the
      // block cache is correctly invalidated and rebuilt.
      $renderArray['#cache']['tags'][] = 'node:' . $nodeRevision['nid'];

      // Check if the user has access to this node and skip displaying it if
      // not.
      if (
        !$this->accessManager->checkNamedRoute($routeName, $routeParameters)
      ) {
        continue;
      }

      /** @var array */
      $item = [
        // #attributes on item_list items applies the attributes to list item
        // content, not the list item itself, but #wrapper_attributes applies
        // attributes to the list item itself.
        '#wrapper_attributes' => [
          'class' => [$listClass . '__item'],
        ],
      ];

      // The revision node content, containing the date in a <time> element.
      /** @var array */
      $itemContent = [
        'date'  => [
          '#type'         => 'html_tag',
          '#tag'          => 'time',
          '#attributes'   => [
            'class'         => [$listClass . '__item-date'],
            'datetime'      => $this->timeline->getDateFormatted(
              $nodeRevision['date'], 'html'
            ),
          ],
          '#value'        => $this->timeline->getDateFormatted(
            $nodeRevision['date'], 'short'
          ),
        ],
      ];

      if ($nodeRevision['published'] === false) {
        // Add a line break between the date and the unpublished indicator.
        $itemContent['break'] = [
          '#type'   => 'html_tag',
          '#tag'    => 'br',
          '#value'  => '',
          '#attributes' => [
            'class'       => [$listClass . '__item-break'],
          ],
        ];
        // The unpublished indicator.
        $itemContent['unpublished'] = [
          '#type'   => 'html_tag',
          '#tag'    => 'em',
          '#value'  => $this->t('(unpublished)'),
          '#attributes' => [
            'class'       => [$listClass . '__item-status'],
          ],
        ];

        $item['#wrapper_attributes']['class'][] =
          $listClass . '__item--unpublished';
      }

      // Is this the current route's node? If so, only output the content
      // without a link.
      if ($nodeRevision['nid'] === (int) $node->nid->getString()) {
        $item = NestedArray::mergeDeep($item, $itemContent);

        $item['#wrapper_attributes']['class'][] =
          $listClass . '__item--current';

      // If this isn't the current node, output a link.
      } else {
        $item['#type']  = 'link';
        $item['#url']   = Url::fromRoute($routeName, $routeParameters);
        $item['#title'] = $itemContent;
      }

      $renderArray['revision_list']['#items'][] = $item;
    }

    return $renderArray;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(
      parent::getCacheContexts(),
      ['omnipedia_wiki_node', 'user.permissions']
    );
  }

}
