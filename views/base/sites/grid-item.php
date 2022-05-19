<?php
/**
 * grid item view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-border-transparent" tabindex="0">

  <div class="wu-grid-item wu-border wu-border-solid wu-border-gray-300 wu-pb-8 wu-bg-white">

    <div class="wu-relative wu-bg-gray-100" style="max-height: 220px; overflow: hidden;">

      <img
        style="opacity: 0.6;"
        class="wu-w-full wu-h-auto wu-image-preview"
        data-image="<?php echo $item->get_featured_image('large'); ?>"
        src="<?php echo $item->get_featured_image('wu-thumb-medium'); ?>"
      />

      <?php if (current_user_can('wu_read_sites')) : ?>

        <div class="wu-my-4 wu-mx-3 wu-inline-block wu-absolute wu-bottom-0 wu-right-0 wu-rounded wu-px-2 wu-py-1 wu-uppercase wu-text-xs wu-font-bold <?php echo esc_attr($item->get_type_class()); ?>">
          <?php echo $item->get_type_label(); ?>
        </div>

      <?php endif; ?>

    </div>

    <div class="wu-text-base wu-px-3 wu-my-3">

      <div>
        <span class="wu-font-semibold"><?php echo $item->get_title(); ?></span>
        <small><?php echo $item->get_id() ? '#'.$item->get_id() : ''; ?></small>
      </div>

      <div class="wu-text-xs wu-my-1">
        <a class="wu-no-underline" href="<?php echo $item->get_active_site_url(); ?>"><?php echo $item->get_active_site_url(); ?></a>
      </div>

    </div>

    <div class="wu-flex wu-justify-between wu-items-center wu--mb-8 wu-p-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-border-l-0 wu-border-r-0 wu-border-b-0">

        <?php if ($item->get_type() !== 'main') : ?>

          <?php if ($item->get_type() === 'pending') : ?>

            <label>
              <input class="wu-rounded-none" type="checkbox" name="bulk-delete[]" value="<?php echo $item->get_membership_id(); ?>" />
              <?php _e( 'Select Site', 'wp-ultimo' ); ?>
            </label>

            <a title="<?php echo esc_attr(__('Publish pending site', 'wp-ultimo')); ?>" href="<?php echo wu_get_form_url('publish_pending_site', array('membership_id' => $item->get_membership_id())); ?>" class="wubox button button-primary">
              <?php _e('Publish Site', 'wp-ultimo'); ?>
            </a>

          <?php else : ?>

            <label>
              <input class="wu-rounded-none" type="checkbox" name="bulk-delete[]" value="<?php echo $item->get_id(); ?>" />
              <?php _e( 'Select Site', 'wp-ultimo' ); ?>
            </label>

            <a href="<?php echo wu_network_admin_url('wp-ultimo-edit-site', array('id' => $item->get_id())); ?>" class="button button-primary">
              <?php _e('Manage', 'wp-ultimo'); ?>
            </a>

          <?php endif; ?>

        <?php else : ?>

          <span>&nbsp;</span>

          <a href="<?php echo wu_network_admin_url('wp-ultimo-edit-site', array('id' => $item->get_id())); ?>" class="button button-primary">
            <?php _e('See Main Site', 'wp-ultimo'); ?>
          </a>

        <?php endif; ?>

    </div>
  </div>
</div>
