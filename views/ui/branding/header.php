<?php
/**
 * Branding header view.
 *
 * @since 2.0.0
 */
?>
<div id="wp-ultimo-header" class="wu-px-4 wu--ml-5 wu-bg-white wu-border-0 wu-border-gray-300 wu-border-b wu-border-solid wu-pb-3 sm:wu-py-3 sm:wu-pt-3 wu-hidden sm:wu-flex">

  <div class="wu-w-1/2 wu-self-center">

    <span class="dashicons-before dashicons-wu-wp-ultimo"></span>

    <span class="wu-text-gray-600 wu-uppercase wu-text-xs wu-font-bold wu-align-middle wu-ml-2 wu-hidden md:wu-inline-block">

      <?php if (WP_Ultimo()->is_loaded()) : ?>

        <?php

        $flags = array(
          'alpha',
          'beta',
          'rc',
        );

        $has_flag = false;

        $version = wu_get_version();
        
        foreach ($flags as $flag) {

          if ($has_flag) {

            continue;

          } // end if;

          $has_flag = strpos($version, $flag) !== false;

        } // end foreach;

        if (!$has_flag) {

          return;

        } // end if;
        
        ?>

        <a 
          target="_blank" 
          href="https://wpultimo.feedbear.com/boards/v2-beta-bug-reports" 
          class="wu-text-red-500 wu-ml-2 wu-no-underline"
        >
          <span class="dashicons-wu-warning"></span>
          <?php _e('Report a Bug', 'wp-ultimo'); ?>
        </a>

        <?php

        /**
         * Allow plugin developers to add more elements on left header container.
         *
         * @since 2.0.0
         *
         * @param \WP_Ultimo\Admin_Pages\Base_Admin_Page $page WP Ultimo admin page instance.
         */
        do_action('wu_header_left', $page);

        ?>

      <?php endif; ?>

    </span>

  </div>

  <div class="wu-w-1/2 wu-text-right wu-self-center">

    <?php if (WP_Ultimo()->is_loaded()) : ?>

      <?php

      /**
       * Allow plugin developers to add more elements on right header container.
       *
       * @since 2.0.0
       *
       * @param \WP_Ultimo\Admin_Pages\Base_Admin_Page $page WP Ultimo admin page instance.
       */
      do_action('wu_header_right', $page);

      ?>

      <small class="wu-ticker-container wu-hidden md:wu-inline-block">
        <strong>
          <span class="wu-inline-block wu-bg-gray-200 wu-rounded-full wu-py-1 wu-pl-2 wu-pr-3 wu-uppercase">
            <span title="<?php esc_attr_e('Server Clock', 'wp-ultimo'); ?>" class="dashicons dashicons-wu-clock wu-text-sm wu-w-auto wu-h-auto wu-align-text-top wu-mr-1 wu-relative"></span>
            <span id="wu-ticker" class="wu-font-mono wu-font-normal">
              <?php echo gmdate('Y-m-d H:i:s', current_time('timestamp')); ?>
            </span>
          </span>
        </strong>
      </small>

    <?php endif; ?>

  </div>

</div>
