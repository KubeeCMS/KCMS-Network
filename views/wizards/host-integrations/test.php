<?php
/**
 * Test view.
 *
 * @since 2.0.0
 */
?>
<h1><?php _e('Testing the Integration', 'wp-ultimo'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6">
  <?php printf(__('We will send a test API call to %s to make sure we are able to connect. This will confirm if everything we did so far have worked.', 'wp-ultimo'), $integration->get_title()); ?>
</p>

<div id="integration-test">

  <div v-if="loading" class="wu-flex wu-rounded wu-content-center wu-py-2 wu-px-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-m-0">
    <span class="dashicons dashicons-warning wu-text-blue-400 wu-self-center wu-mr-2"></span>
    <span>
      <?php _e('Sending API call...', 'wp-ultimo'); ?>
    </span>
  </div>

  <div v-cloak v-if="!loading && success" class="wu-flex wu-rounded wu-content-center wu-py-2 wu-px-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-m-0">
    <span class="dashicons dashicons-yes-alt wu-text-green-400 wu-self-center wu-mr-2"></span>
    <span>
      <?php _e('Yey! Everything seems to be working!', 'wp-ultimo'); ?>
    </span>
  </div>

  <div v-cloak v-if="!loading && !success" class="wu-flex wu-rounded wu-content-center wu-py-2 wu-px-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-m-0">
    <span class="dashicons dashicons-dismiss wu-text-red-400 wu-self-center wu-mr-2"></span>
    <span>
      <?php _e('Something wrong happened... We might need to make some adjustments to make this work.', 'wp-ultimo'); ?>
    </span>
  </div>

  <pre class="wu-overflow-auto wu-p-4 wu-rounded wu-content-center wu-bg-gray-800 wu-text-white wu-font-mono wu-border wu-border-solid wu-border-gray-300" v-html="results"><?php _e('Waiting for results...', 'wp-ultimo'); ?></pre>

  <div v-cloak v-if="!loading && !success">

    <h1><?php _e('Troubleshooting', 'wp-ultimo'); ?></h1>

    <ol>
      <li>
        <?php _e('Go back to the <strong>Configuration</strong> step - if available - and make sure you entered all the necessary information correctly;', 'wp-ultimo'); ?>
      </li>
      <li>
        <?php _e('If you have added the constants to your wp-config.php file manually, double check to make sure you\'ve added them to the right wp-config.php file and in the right place (just above the <code>/* That\'s all, stop editing! Happy publishing. */', 'wp-ultimo'); ?></code>);
      </li>
      <li>
        <?php _e('If you are sure everything is right, take a screenshot of this screen and contact support.', 'wp-ultimo'); ?>
      </li>
    </ol>

  </div>

  <!-- Submit Box -->
  <div v-cloak v-if="!loading && !success" class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

    <a href="<?php echo esc_url($page->get_prev_section_link()); ?>" class="wu-self-center button button-large wu-float-left">
      <?php _e('&larr; Go Back', 'wp-ultimo'); ?>
    </a>

  </div>
  <!-- End Submit Box -->

  <div v-cloak v-if="!loading && success">
    <?php
    /**
     * Default Submit box
     */
    $page->render_submit_box();
    ?>
  </div>

</div>

<script>
  (function($) {
    $(document).ready(function() {

      new Vue({
        el: "#integration-test",
        data: {
          success: false,
          loading: false,
          results: '<?php echo esc_js(__('Waiting for results...', 'wp-ultimo')); ?>',
        },
        mounted: function() {
          var that = this;
          this.loading = true;
          $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
              action: 'wu_test_hosting_integration',
              integration: '<?php echo esc_js($integration->get_id()); ?>',
            },
            success: function(response) {
              console.log(response);
              that.loading = false;
              that.success = response.success;
              that.results = response.data;
            }
          })
        },
      });

    });
  })(jQuery);
</script>

