/* global ajaxurl, wu_block_ui */
(function($) {

  $(document).ready(function() {

    /**
     * Listen for change events.
     */
    $('.wu-domain-primary').on('change', function() {

      const el = $(this);

      const id = el.val();

      const checked = el.is(':checked');

      const block = wu_block_ui('#wp-ultimo-domain-mapping-element .inside');

      $.ajax({
        url: ajaxurl,
        data: {
          action: 'wu_toggle_primary',
          id,
          value: checked,
        },
        success(data) {

          el.attr('checked', data.status);

          block.unblock();

        },
        error() {

          block.unblock();

        },
      });

    });

  });

}(jQuery));
