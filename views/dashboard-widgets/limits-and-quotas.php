<?php
/**
 * Limits and quotas view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

  <div class="<?php echo wu_env_picker('', 'wu-widget-inset'); ?>">

    <!-- Title Element -->
    <div class="wu-p-4 wu-flex wu-items-center <?php echo wu_env_picker('', 'wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-b wu-border-gray-200'); ?>">

      <?php if ($title) : ?>

        <h3 class="wu-m-0 <?php echo wu_env_picker('', 'wu-widget-title'); ?>">

          <?php echo $title; ?>

        </h3>

      <?php endif; ?>

    </div>
    <!-- Title Element - End -->

    <ul class="wu-list-none wu-m-0 wu-p-0 wu-grid wu-gap-5 wu-row-gap-0 lg:wu-grid-cols-<?php echo esc_attr((int) $columns); ?> <?php echo wu_env_picker('', 'wu-p-4'); ?>">

      <?php foreach ($post_types as $post_type_slug => $post_type) : ?>

        <?php if ($site->get_quota($post_type_slug) !== false) : ?>

          <?php

            $post_count_status = apply_filters('wu_post_count_status', array('publish'), $post_type);

            $post_count = $post_type_slug === 'attachment' ? $post_count->inherit : $site->get_post_count($post_type_slug);

            // Filter Post Count for custom added things
            $post_count = apply_filters('wu_post_count', $post_count, $post_type_slug);

            // Calculate width
            if ($site->get_quota($post_type_slug) == 0) {

              $width = 5;

            } else {

              $width = ($post_count / $site->get_quota($post_type_slug) * 100);

            } // end if;

          ?>

          <li class="wu-py-2 wu-m-0">

            <span class="wu-text-sm">

              <?php echo $post_type->label; ?>

            </span>

            <span class="wu-w-full wu-bg-gray-200 wu-rounded-full wu-h-1 wu-block wu-my-1">

              <span class="wu-bg-red-500 wu-rounded-full wu-h-1 wu-block wu-my-1" style="width: <?php echo $width; ?>%;"></span>

            </span>

            <div class="wu-text-sm wu-text-gray-600 wu-align-middle">

              <?php echo $post_count; ?>
              /
              <?php echo $site->get_quota($post_type_slug) == 0 ? __('Unlimited', 'wp-ultimo') : $site->get_quota($post_type_slug); ?>

            </div>

          </li>

        <?php endif; ?>

      <?php endforeach; ?>

      <?php if ($site->should_display_quota('visits')) : ?>

        <?php

          /*
            * Get the visits count.
            */
          $visits_count = (int) $site->get_visits_count();

          /*
            * Calculates the width of the bar
            */
          $visits_width = empty($site->get_quota('visits')) ? 1 : $visits_count / $site->get_quota('visits') * 100;

        ?>

        <li class="quota wu-py-2 wu-m-0">

          <div class="wu-text-sm">

            <?php _e('Unique Visits', 'wp-ultimo'); ?>

            <?php echo wu_tooltip(sprintf(__('Next Reset: %s', 'wp-ultimo'), date_i18n(get_option('date_format', 'd/m/Y'), strtotime('last day of this month')))); ?>

          </div>

          <span class="wu-w-full wu-bg-gray-200 wu-rounded-full wu-h-1 wu-block wu-my-1">

            <span class="wu-bg-red-500 wu-rounded-full wu-h-1 wu-block wu-my-1" style="width: <?php echo $visits_width; ?>%;"></span>

          </span>

          <div class="wu-text-sm wu-text-gray-600 wu-align-middle">

            <?php echo number_format($visits_count); ?>
            /
            <?php echo $site->get_quota('visits') == 0 ? __('Unlimited', 'wp-ultimo') : number_format($site->get_quota('visits')); ?>
            
          </div>

        </li>

      <?php endif; ?>

    </ul>

  </div>

</div>
