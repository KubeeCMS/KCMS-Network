/* global Vue, wu_block_ui */
(function($) {

  /**
   * If we detect the preview changed, we add a loader.
   */
  window.addEventListener('message', function(message) {

    let block;

    if (message.data === 'wu_preview_changed') {

      block = wu_block_ui('#preview_content');

    } // end if;

    /**
     * Unblocks when the loading is finished.
     */
    $('#preview-stage-iframe').on('load', function() {

      if (block) {

        block.unblock();

      } // end if;

    });

  }, false);

  $(document).ready(function() {

    // eslint-disable-next-line no-unused-vars
    const wu_preview_stage = new Vue({
      el: '#preview-stage',
      data() {

        return {
          preview: false,
        };

      },
    });

    $('[data-wu-customizer-panel]').each(function() {

      const app = $(this);

      const app_name = 'wu_' + app.data('wu-app');

      wp.hooks.addAction(app_name + '_changed', 'nextpress/wp-ultimo', function(prop) {

        if (prop === 'tab') {

          return;

        } // end if;

        const param = jQuery.param(window[app_name].$data);

        const param2 = app.find('input').serialize();

        const url = $('#preview-stage-iframe').attr('data-src');

        $('#preview-stage-iframe').attr('src', url + '&' + param + '&' + param2);

      });

    });

  });

}(jQuery));
