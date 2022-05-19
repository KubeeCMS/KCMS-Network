<?php
/**
 * My Sites
 *
 * @since 2.0.0
 */

$current_site = wu_get_current_site();

$add_new_url = $current_site->get_membership() &&  $current_site->get_membership()->has_remaining_sites() ? admin_url('admin.php?page=add-new-site') : wu_get_registration_url();

?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

  <div class="<?php echo wu_env_picker('wu-mb-4', ''); ?>">

    <div class="wu-relative">

      <div
        class="wu-grid wu-gap-5 wu-grid-cols-<?php echo esc_attr((int) $columns); ?> sm:wu-grid-cols-<?php echo esc_attr((int) $columns); ?> xl:wu-grid-cols-<?php echo esc_attr((int) $columns); ?> lg:wu-max-w-none <?php echo wu_env_picker('', 'wu-py-4'); ?>">

        <?php foreach ($sites as $site) : ?>

          <div class="wu-flex wu-flex-col wu-rounded-lg wu-overflow-hidden wu-border-solid wu-border wu-border-gray-300">

            <div class="wu-flex-shrink-0">

              <div class="wu-absolute wu-m-2">

                <?php if ($site->get_membership()) : ?>

                  <span
                    class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium <?php echo $site->get_membership()->get_status_class(); ?>"
                  >
                    <?php echo $site->get_membership()->get_status_label(); ?>
                  </span>

                <?php endif; ?>
                
                <!-- <span
                  class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium wu-bg-yellow-200 wu-text-yellow-800">
                  <span class="dashicons-wu-warning wu-mr-1 wu-text-xs"></span>
                  Billing Issues
                </span> -->

                <?php if ($site->is_customer_primary_site()) : ?>
                
                  <span
                    class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium wu-bg-gray-800 wu-text-gray-300">
                    <?php _e('Primary', 'wp-ultimo'); ?>
                  </span>

                <?php endif; ?>

                <!-- <span
                  class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium wu-bg-red-100 wu-text-red-800">
                  <span class="dashicons-wu-warning wu-mr-1 wu-text-xs"></span>
                  Offline
                </span> -->

              </div>

              <?php if ($display_images) : ?>
              
                <img 
                  class="wu-h-48 wu-w-full wu-object-cover wu-block"
                  src="<?php echo $site->get_featured_image(); ?>" 
                  alt="<?php printf(esc_attr__('Site Image: %s', 'wp-ultimo'), $site->get_title()); ?>"
                  style="background-color: rgba(255, 255, 255, 0.5)"
                >

              <?php else : ?>

                <div class="">&nbsp;</div>

              <?php endif; ?>

            </div>

            <div class="wu-flex-1 wu-bg-white wu-py-6 wu-px-4 wu-flex wu-flex-col wu-justify-between">

              <div class="wu-flex-1">

                <a href="<?php echo esc_attr($site->get_active_site_url()); ?>" class="wu-block wu-no-underline">

                  <span class="wu-text-base wu-font-semibold wu-text-gray-800 wu-block" <?php echo wu_tooltip_text(__('Visit Site', 'wp-ultimo')); ?>>
                    <?php echo $site->get_title(); ?> <span class="wu-text-sm dashicons-wu-popup"></span>
                  </span>

                  <span class="wu-text-xs wu-text-gray-600 wu-block wu-mt-2">
                    <?php echo str_replace(array('http://', 'https://'), '', $site->get_active_site_url()); ?>
                  </span>

                </a>

              </div>

            </div>
            <ul
              class="wu-p-0 wu-m-0 wu-px-4 wu-text-center wu-py-2 wu-my-0 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">

              <?php if (WP_Ultimo()->currents->get_site() && WP_Ultimo()->currents->get_site()->get_id() == $site->get_id()) : ?>

                <li class="wu-block wu-my-2">
                  <span
                    class="wu-w-full wu-no-underline <?php echo wu_env_picker('wu-text-sm', 'button button-primary button-disabled'); ?>">
                    <?php _e('Current Site', 'wp-ultimo'); ?>
                  </span>
                </li>

              <?php else : ?>

                <li class="wu-block wu-my-2">
                  <a href="<?php echo esc_url($element->get_manage_url($site->get_id())); ?>"
                    class="wu-w-full wu-no-underline <?php echo wu_env_picker('wu-text-sm', 'button button-primary'); ?>">
                    <?php _e('Manage', 'wp-ultimo'); ?>
                  </a>
                </li>

              <?php endif; ?>

            </ul>
          </div>

        <?php endforeach; ?>

        <a href="<?php echo $add_new_url; ?>"
          class="wu-no-underline wu-text-gray-600 wu-flex wu-flex-col wu-rounded-lg wu-border-2 wu-border-dashed wu-border-gray-400 wu-overflow-hidden wu-items-center wu-justify-center"
          style="background-color: rgba(255, 255, 255, 0.1)">

          <span class="wu-text-center wu-p-8">
            <span class="wu-text-3xl dashicons-wu-circle-with-plus"></span>
            <span class="wu-text-lg wu-mt-2 wu-block"><?php _e('Add new Site', 'wp-ultimo'); ?></span>
          </span>

        </a>

      </div>

    </div>

  </div>

</div>

<!-- <div class="md:wu-grid-cols-4"></div> -->
<!-- <div class="md:wu-grid-cols-5"></div> -->
<!-- <div class="md:wu-grid-cols-6"></div> -->
