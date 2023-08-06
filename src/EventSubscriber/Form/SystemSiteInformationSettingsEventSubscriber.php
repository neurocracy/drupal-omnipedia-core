<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\EventSubscriber\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\omnipedia_core\Entity\Node as WikiNode;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter the site settings form to enforce Omnipedia front page requirements.
 */
class SystemSiteInformationSettingsEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The Drupal string translation service.
   */
  public function __construct(
    protected readonly WikiNodeResolverInterface $wikiNodeResolver,
    protected $stringTranslation,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_system_site_information_settings.alter' => 'formAlter',
    ];
  }

  /**
   * Alter the 'system_site_information_settings' form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event object.
   */
  public function formAlter(FormIdAlterEvent $event): void {
    /** @var array */
    $form = &$event->getForm();

    // Update the front page field description to include our requirements.
    // Note that a render array can also work here, including the use of an
    // 'inline_template'.
    $form['front_page']['site_frontpage']['#description'] = $this->t(
      'Specify a relative URL to display as the default front page. Note that this must point to @contentLink for Omnipedia to function correctly.',
      [
        // Unfortunately, there doesn't seem to be an alternative to rendering
        // the link here, as even a render array from Link::toRenderable()
        // causes warnings. It's preferable to create a link programmatically
        // rather than embedding an <a> element in the text, as the former can
        // be altered via Drupal's hooks while the latter cannot.
        '@contentLink' => Link::createFromRoute(
          $this->t('an existing wiki page'),
          'view.content.page_1',
          ['type' => WikiNode::getWikiNodeType()]
        )->toString(),
      ]
    );

    // Add our validation method.
    $form['front_page']['site_frontpage']['#element_validate'][] = [
      $this, 'validateFrontPageElement'
    ];
  }

  /**
   * Validate the site front page form element.
   *
   * This ensures that the front page cannot be set to anything other than a
   * wiki page node.
   *
   * @param array &$element
   *   The element being validated.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   *
   * @param array &$form
   *   The whole form.
   */
  public function validateFrontPageElement(
    array &$element, FormStateInterface $formState, array &$form
  ): void {
    if (empty($element['#value'])) {
      $formState->setErrorByName(
        'site_frontpage', $this->t('This cannot be empty.')
      );

      return;
    }

    /** @var \Drupal\Core\Url */
    $urlObject = Url::fromUserInput($element['#value']);

    // Check that the built URL actually points to a route that exists to avoid
    // a fatal error when we try to get the parameters to a non-existent route.
    if (!$urlObject->isRouted()) {
      $formState->setErrorByName(
        'site_frontpage',
        $this->t('This must point to an existing, internal site content.')
      );

      return;
    }

    /** @var array */
    $routeParameters = $urlObject->getRouteParameters();

    if (
      empty($routeParameters['node']) ||
      !$this->wikiNodeResolver->isWikiNode($routeParameters['node'])
    ) {
      $formState->setErrorByName(
        'site_frontpage',
        $this->t('This must point to an existing wiki page.')
      );
    }
  }

}
