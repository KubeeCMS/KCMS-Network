/* global wutb_remove, wu_block_ui, wu_modal_refresh, wu_initialize_editors, wu_initialize_forms */
(function($) {

  window.wu_initialize_forms = function() {

    const form_id = $('.wu_form').attr('id');

    jQuery(document).on('wu_' + form_id + '_changed', wu_modal_refresh);

    jQuery(document).on('wu_' + form_id + '_errors_changed', wu_modal_refresh);

    $('.wu_form').on('submit', function(e) {

      if (jQuery('textarea[data-editor]')) {

        const editor_id = jQuery('textarea[data-editor]').attr('id');

        const editor_val = jQuery('textarea[data-editor]').val();

        jQuery('input[name="' + editor_id + '"]').val(editor_val);

      } // end if;

      const blocked_form = wu_block_ui(this);

      /*
       * Clean errors
       */
      window['wu_' + form_id + '_errors'].errors = [];

      e.preventDefault();

      let submit_button = '';

      try {

        submit_button = $(e.originalEvent.submitter).val();

      } catch ($error) { }

      const form_data = $('.wu_form').serialize() + '&submit=' + submit_button;

      $.post($('.wu_form').attr('action'), form_data, function(results) {

        if (results.success === true) {

          /*
           * Refresh tables if we get that back
           */
          if (typeof results.data.tables === 'object') {

            /*
             * Unblock the form
             */
            blocked_form.unblock();

            /*
             * Close thickbox
             */
            wutb_remove();

            $.each(results.data.tables, function(index, item) {

              window[item].update();

            });

          } // end if;

          /*
           * Redirect of we get a redirect URL back.
           */
          if (typeof results.data.redirect_url === 'string') {

            window.location.href = results.data.redirect_url;

          } // end if;

          /*
           * Redirect of we get a redirect URL back.
           */
          if (typeof results.data.send === 'object') {

            window[results.data.send.scope][results.data.send.function_name](results.data.send.data, () => {

              /*
               * Close thickbox
               */
              wutb_remove();

            });

          } // end if;

        } else {

          /*
           * Unblock the form
           */
          blocked_form.unblock();

          /*
           * On failure, display errors.
           */
          window['wu_' + form_id + '_errors'].errors = results.data;

          jQuery('[data-wu-app="' + form_id + '_errors' + '"]').attr('tabindex', -1).focus();

        } // end if;

      });

    });

  };

  $(document).ready(function() {

    jQuery('body').on('wubox:load', function() {

      wu_initialize_editors();

      wu_initialize_forms();

    });

  });

}(jQuery));
