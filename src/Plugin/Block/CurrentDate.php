<?php

namespace Drupal\omnipedia_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\omnipedia_core\Service\TimelineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Current date block.
 *
 * This displays a <time> element with the current Omnipedia date.
 *
 * @Block(
 *   id           = "omnipedia_current_date",
 *   admin_label  = @Translation("Current date"),
 *   category     = @Translation("Omnipedia"),
 * )
 */
class CurrentDate extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The Omnipedia timeline service.
   *
   * @var \Drupal\omnipedia_core\Service\TimelineInterface
   */
  private $timeline;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\omnipedia_core\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   */
  public function __construct(
    array $configuration, string $pluginID, array $pluginDefinition,
    TimelineInterface $timeline
  ) {
    parent::__construct($configuration, $pluginID, $pluginDefinition);

    // Save dependencies.
    $this->timeline = $timeline;
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
      $container->get('omnipedia.timeline')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $dateFormattedStorage = $this->timeline->getDateFormatted('current', 'storage');

    return [
      // This needs to be wrapped in its own key so that #attributes doesn't get
      // removed by the render/block system for some reason.
      'current_date'  => [
        '#type'         => 'html_tag',
        '#tag'          => 'time',
        '#attributes'   => [
          'class'         => ['omnipedia-current-date'],
          'datetime'      => $dateFormattedStorage,
        ],
        '#value'        => $this->timeline->getDateFormatted('current', 'long'),
      ],
      '#cache'      => [
        // This gets cached permanently, varying by the storage-formatted date,
        // i.e. different cache context for each date.
        'contexts'    => ['omnipedia_dates'],
        'tags'        => ['omnipedia_dates:' . $dateFormattedStorage],
        'max-age'     => Cache::PERMANENT,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(
      parent::getCacheContexts(),
      ['omnipedia_dates']
    );
  }

}
