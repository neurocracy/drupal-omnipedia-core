<?php

declare(strict_types=1);

namespace Drupal\omnipedia_core\Service;

use Drupal\omnipedia_core\Entity\NodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeViewedInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The Omnipedia wiki node viewed service.
 */
class WikiNodeViewed implements WikiNodeViewedInterface {

  /**
   * The Symfony session attribute key where we store the recently viewed nodes.
   *
   * @see https://symfony.com/doc/3.4/components/http_foundation/sessions.html#namespaced-attributes
   */
  protected const RECENT_WIKI_NODES_SESSION_KEY = 'omnipedia/recentWikiNodes';

  /**
   * The number of recent wiki nodes to track for a user.
   *
   * @todo Should this be stored in config and given an admin form?
   */
  protected const RECENT_WIKI_NODES_COUNT = 5;

  /**
   * Constructs this service object; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The Symfony session service.
   */
  public function __construct(
    protected readonly WikiNodeResolverInterface  $wikiNodeResolver,
    protected readonly SessionInterface           $session,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function addNode(NodeInterface|int|string $node): void {
    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveWikiNode($node);

    // Return if this is not a wiki node.
    if (\is_null($node)) {
      return;
    }

    /** @var string */
    $nid = $node->nid->getString();

    /** @var array */
    $viewedNids = $this->getNodes();

    // Bail if the nid is already the most recent in the viewed array so that we
    // don't record it twice. This is to guard against erroneously calling this
    // more than once during a redirect or similar situation.
    if (\end($viewedNids) === $nid) {
      return;
    }

    $viewedNids[] = $nid;

    // Remove any viewed nids from the end of the array that exceed the recent
    // wiki nodes count limit. Note that array_slice() correctly handles array
    // lengths lower than or equal to the provided length parameter by returning
    // the array as-is with no changes.
    $viewedNids = \array_reverse(\array_slice(
      \array_reverse($viewedNids), 0, self::RECENT_WIKI_NODES_COUNT
    ));

    // Save to session storage.
    $this->session->set(self::RECENT_WIKI_NODES_SESSION_KEY, $viewedNids);
  }

  /**
   * {@inheritdoc}
   *
   * @see self::RECENT_WIKI_NODES_SESSION_KEY
   *   Session key where array is stored.
   */
  public function getNodes(): array {
    if ($this->session->has(self::RECENT_WIKI_NODES_SESSION_KEY)) {
      return $this->session->get(self::RECENT_WIKI_NODES_SESSION_KEY);
    } else {
      return [];
    }
  }

}
