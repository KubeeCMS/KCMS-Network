<?php
/**
 * Account summary view.
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

      <?php if (wu_request('page') !== 'account') : ?>

        <div class="wu-ml-auto">

          <a 
            title="<?php esc_attr_e('See More', 'wp-ultimo'); ?>" 
            class="wu-text-sm wu-no-underline button" 
            href="<?php echo $element->get_manage_url($site->get_id()); ?>"
          >

            <?php _e('See More', 'wp-ultimo'); ?>

          </a>

        </div>

      <?php endif; ?>

    </div>
    <!-- Title Element - End -->

    <ul class="md:wu-flex wu-m-0 wu-list-none wu-p-4">

    <?php if ($product) : ?>

      <li class="wu-flex-1 wu-relative wu-m-0">

        <div>

          <strong class="wu-text-gray-800 wu-text-base">

		        <?php echo $product->get_name(); ?>

          </strong>

        </div>

        <div class="wu-text-sm wu-text-gray-600">
          <span class="wu-block"><?php _e('Your current plan', 'wp-ultimo'); ?></span>
          <!-- <a href="#" class="wu-no-underline"><?php _e('Manage &rarr;', 'wp-ultimo'); ?></a> -->
        </div>

      </li>

    <?php endif; ?>

    <?php if ($site_trial) : ?>

    <li class="wu-flex-1 wu-relative wu-m-0">

      <div>

        <strong class="wu-text-gray-800 wu-text-base">
		      <?php printf(_n('%s day', '%s days', $site_trial, 'wp-ultimo'), $site_trial); ?>
        </strong>

      </div>

      <div class="wu-text-sm wu-text-gray-600">
        <span class="wu-block"><?php _e('Remaining time in trial', 'wp-ultimo'); ?></span>
        <!-- <a href="#" class="wu-no-underline"><?php _e('Upgrade &rarr;', 'wp-ultimo'); ?></a> -->
      </div>

    </li>

    <?php endif; ?>

    <li class="wu-flex-1 wu-relative wu-m-0">

      <div>

        <strong class="wu-text-gray-800 wu-text-base">
          <?php
			/**
			 * Display space used
			 */
			printf($message, size_format($space_used), size_format($space_allowed));
			?>
        </strong>

        <?php if (!$unlimited_space) : ?>

          <span class="wu-p-1 wu-bg-gray-200 wu-inline wu-align-text-bottom wu-rounded wu-text-center wu-text-xs wu-text-gray-600">
            <?php echo $percentage; ?>%
          </span>

        <?php endif; ?>

      </div>

      <div class="wu-text-sm wu-text-gray-600">
        <span class="wu-block"><?php _e('Disk space used', 'wp-ultimo'); ?></span>
        <!-- <a href="#" class="wu-no-underline"><?php _e('Upgrade &rarr;', 'wp-ultimo'); ?></a> -->
      </div>

    </li>

  </ul>

</div>

</div>
