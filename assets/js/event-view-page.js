/* eslint-disable no-unused-vars */
/* global Vue, wu_event_view_payload, ClipboardJS */
(function($) {

  $(document).ready(function() {

    wu_event_view_payload = new Vue({
      el: '#wu_payload',
      data() {

        return {
          loading: false,
          payload: $('#hidden_textarea').val(),
        };

      },
      methods: {},

    });

    // eslint-disable-next-line no-unused-vars
    const clipboard = new ClipboardJS('.btn-clipboard');

    clipboard.on('success', function(e) {

      const target = $(e.trigger);

      const default_text = target.text();

      target.attr('disabled', 'disabled').text('Copied!');

      setTimeout(function() {

        target.text(default_text).removeAttr('disabled');

      }, 3000);

    });

  });

}(jQuery));
