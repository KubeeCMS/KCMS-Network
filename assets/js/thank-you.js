/* global Vue, wu_thank_you, wu_transition_text */
(function($) {

  $(document).ready(function() {

    /**
     * Re-send email activation option.
     */
    $('.wu-resend-verification-email').on('click', function(e) {

      e.preventDefault();

      const transitional_text = wu_transition_text(this, true).text(wu_thank_you.i18n.resending_verification_email, 'wu-text-gray-400');

      $.ajax({
        type: 'POST',
        url: wu_thank_you.ajaxurl,
        data: {
          action: 'wu_resend_verification_email',
          _ajax_nonce: wu_thank_you.resend_verification_email_nonce,
        },
        success(response) {

          if (response.success) {

            transitional_text.text(wu_thank_you.i18n.email_sent, 'wu-text-green-700', true).done();

          } else {

            transitional_text.text(response.data[0].message, 'wu-text-red-600', true).done();

          } // end if;

        },
      });

    });

    /*
     * Vue
     */
    if ($('#wu-sites').length) {

      window.wu_sites = new Vue({
        el: '#wu-sites',
        data: {
          creating: wu_thank_you.creating,
          next_queue: parseInt(wu_thank_you.next_queue, 10) + 5,
          random: 0,
          progress_in_seconds: 0,
        },
        computed: {
          progress() {

            return Math.round((this.progress_in_seconds / this.next_queue) * 100);

          },
        },
        mounted() {

          if (wu_thank_you.has_pending_site) {

            this.check_site_created();

            return;

          } // end if;

          if (this.next_queue <= 0 || wu_thank_you.creating) {

            return;

          } // end if;

          const interval_seconds = setInterval(() => {

            this.progress_in_seconds++;

            if (this.progress_in_seconds >= this.next_queue) {

              clearInterval(interval_seconds);

              window.location.reload();

            } // end if;

            if (this.progress_in_seconds % 5 === 0) {

              fetch('/wp-cron.php?doing_wp_cron');

            } // end if;

          }, 1000);

        },
        methods: {
          check_site_created() {

            $.ajax({
              type: 'GET',
              url: wu_thank_you.ajaxurl,
              data: {
                action: 'wu_check_pending_site_created',
                membership_hash: wu_thank_you.membership_hash 
              },
              success: (response) => {

                if(response.publish_status === 'stopped' && this.creating === true) {

                  window.location.reload();

                } else {

                  this.creating = response.publish_status === 'running';

                  // if not created, recheck after 3 seconds
                  setTimeout(this.check_site_created, 3000);

                } // end if;
      
              },
            });

          },
        },
      });

    } // end if;

  });

}(jQuery));
