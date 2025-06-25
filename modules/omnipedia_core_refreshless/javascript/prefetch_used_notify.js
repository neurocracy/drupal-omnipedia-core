// -----------------------------------------------------------------------------
//   Omnipedia RefreshLess prefetch used notify component
// -----------------------------------------------------------------------------
AmbientImpact.addComponent(
  'OmnipediaRefreshlessPrefetchUsedNotify',
function(component, $) {

  'use strict';

  /**
   * Event namespace name.
   *
   * @type {String}
   */
  const eventNamespace = component.getName();

  component.addBehaviour(
    component.getName(),
    'omnipedia-refreshless-prefetch-used-notify',
    'body',
    function(context, settings) {

      $(this).on(`refreshless:prefetch-used.${eventNamespace}`, (event) => {

        event.detail.notifyBackend();

      });

    },
    function(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      $(this).off(`refreshless:prefetch-used.${eventNamespace}`);

    },
  );

});
