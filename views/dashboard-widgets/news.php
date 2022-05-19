<?php
/**
 * News view.
 *
 * @since 2.0.0
 */
?>

<div class="wu-styling">

  <div class="rss-widget">

    <div class='wu-rss-widget-title wu-uppercase wu-font-semibold wu-text-gray-600 wu-text-xs wu-mb-3 wu-py-1'><?php _e('From the Community', 'wp-ultimo'); ?></div>

    <div id='wp-ultimo-blog-feed'>

      <div class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4">
        <span class="wu-blinking-animation"><?php _e('Loading...', 'wp-ultimo'); ?></span>
      </div>

    </div>

  </div>

  <div style="margin: 12px -12px -12px;" class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-text-right wu-border-gray-300">

    <a target="_blank" href="<?php echo 'https://community.wpultimo.com/home'; ?>" class="button button-primary wu-w-full wu-text-center">

      <?php _e('Join our Community', 'wp-ultimo'); ?> &rarr;
      
    </a>

  </div>

</div>

<script>

    (function($) {

      $(document).ready(function() {

        /** WP Ultimo Blog */
        $.get({
          url: ajaxurl,
          data: <?php echo json_encode(array(
            'action'       => 'wu_fetch_rss',
            'url'          => 'https://versions.nextpress.co/updates/news.php',
            'title'        => __('WP Ultimo Community', 'wp-ultimo'),
            'items'        => 3,
            'show_summary' => 1,
            'show_author'  => 0,
            'show_date'    => 1,
          )); ?>,
          success: function(response) {

            $('#wp-ultimo-blog-feed').html(response);

          },
          error: function() {

            $('#wp-ultimo-blog-feed').html('<?php echo __("Error loading external feed.", "wp-ultimo"); ?>');

          }
        }); // end get;
      });

    })(jQuery);

</script>
