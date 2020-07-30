<?php

namespace Drupal\omnipedia_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for HTTP 4xx responses.
 */
class Http4xxController extends ControllerBase {

  /**
   * The Drupal link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * Controller constructor; saves dependencies.
   *
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $linkGenerator
   *   The Drupal link generator service.
   */
  public function __construct(LinkGeneratorInterface $linkGenerator) {
    $this->linkGenerator = $linkGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('link_generator')
    );
  }

  /**
   * The 403 content.
   *
   * @return array
   *   A render array containing the message to display for 403 pages.
   *
   * @see \Drupal\system\Controller\Http4xxController::on403()
   *   The original Drupal core controller method this replaces.
   *
   * @see \Drupal\omnipedia_core\EventSubscriber\Routing\ReplaceSystem403ControllerEventSubscriber::alterRoutes()
   *   Replaces the 'system.403' route controller method with this one.
   */
  public function on403() {
    return [
      '#markup' => $this->t(
        'You are not authorised to access this page. Would you like to try @loggingin?',
        [
          '@loggingin' => $this->linkGenerator->generate(
            $this->t('logging in'),
            Url::fromRoute('user.login')
          ),
        ]
      ),
    ];
  }

}
