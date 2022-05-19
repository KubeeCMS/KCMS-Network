<?php
/**
 * The Current Membership
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

      <?php if (wu_request('page') !== 'wu-checkout') : ?>

        <div class="wu-ml-auto">

          <a 
            title="<?php esc_attr_e('Update Billing Address', 'wp-ultimo'); ?>" 
            class="wu-text-sm wu-no-underline button" 
            href="<?php echo esc_attr($element->get_upgrade_form_url($membership->get_hash())); ?>"
          >

            <?php _e('Change', 'wp-ultimo'); ?>

          </a>

        </div>

      <?php endif; ?>

    </div>
    <!-- Title Element - End -->

    <!-- Product Block -->

    <?php if ($plan) : ?>
    
      <div class="wu-p-4 wu-flex wu-justify-between wu-items-center wu-flex-wrap sm:wu-flex-nowrap">

        <div class="">

          <div class="wu-flex wu-items-center">

            <?php if ($display_images && $plan->get_featured_image()) : ?>

              <div class="wu-flex-shrink-0 wu-mr-4">
              
                <img 
                  class="wu-h-8 wu-w-8 wu-rounded" 
                  src="<?php echo esc_url($plan->get_featured_image()); ?>" 
                  alt="<?php echo esc_attr($plan->get_name()); ?>"
                >

              </div>

            <?php endif; ?>

            <div class="">

              <span class="wu-text-lg wu-font-medium wu-text-gray-900 wu-block">

                <?php echo $plan->get_name(); ?>

                <span class="wu-font-mono wu-mx-2 wu-text-xs"><?php echo $membership->get_hash(); ?></span>

              </span>

              <span class="wu-text-sm wu-text-gray-600">
                
                <?php echo $plan->get_price_description(); ?>

              </span>

            </div>

          </div>

          <?php if ($pending_change) : ?>

            <div class="wu-mt-4"> 
              
              <div class="wu-bg-yellow-200 wu-text-yellow-700 wu-rounded wu-p-2">

                <?php printf(__("There's a pending change for this membership, scheduled to take place on <strong>%1\$s</strong>. Changing to <strong>%2\$s</strong>.", 'wp-ultimo'), $pending_change_date, $pending_change); ?>

              </div>

            </div>

          <?php endif; ?>

        </div>

      </div>

    <?php endif; ?>

    <!-- Product Block - End -->

    <?php if ($membership) : ?>

      <div class="wu-py-4 wu-pb-2 wu-px-4 wu-grid wu-grid-cols-1 wu-gap-x-4 wu-gap-y-8 sm:wu-grid-cols-<?php echo esc_attr((int) $columns); ?> wu-border-solid wu-border-0 wu-border-b wu-border-t wu-border-gray-200">

        <div class="sm:wu-col-span-1">

          <div class="wu-text-sm wu-font-medium wu-text-gray-600">
            <?php _e('Status', 'wp-ultimo'); ?>
          </div>

          <div class="wu-mt-1 wu-text-sm wu-text-gray-900 wu-mb-4">
            
            <span class="<?php echo esc_attr($membership->get_status_class()); ?> wu-font-medium wu-inline-block wu-py-1 wu-px-2 wu-rounded">

              <?php echo $membership->get_status_label(); ?>

            </span>

          </div>

        </div>

        <div class="sm:wu-col-span-1">

          <div class="wu-text-sm wu-font-medium wu-text-gray-600">
            <?php _e('Initial Amount', 'wp-ultimo'); ?>
          </div>

          <div class="wu-mt-1 wu-text-sm wu-text-gray-900 wu-mb-4">
            <?php echo wu_format_currency($membership->get_initial_amount(), $membership->get_currency()) ?>
          </div>

        </div>

        <?php if ($membership->is_recurring()) : ?>

          <div class="sm:wu-col-span-1">

            <div class="wu-text-sm wu-font-medium wu-text-gray-600">
              <?php _e('Times Billed', 'wp-ultimo'); ?>
            </div>

            <div class="wu-mt-1 wu-text-sm wu-text-gray-900 wu-mb-4">
              <?php echo $membership->get_times_billed_description(); ?> 
            </div>

          </div>

        <?php endif; ?>

        <?php if (!$membership->is_lifetime()) : ?>

          <div class="sm:wu-col-span-1">

            <div class="wu-text-sm wu-font-medium wu-text-gray-600">
              <?php _e('Expires', 'wp-ultimo'); ?>
            </div>

            <div class="wu-mt-1 wu-text-sm wu-text-gray-900 wu-mb-4">
              <?php echo $membership->get_formatted_date('date_expiration'); ?>
            </div>

          </div>

        <?php endif; ?>

      </div>

      <!-- Additional Packages -->

      <div class="wu-hidden">
        
        <ul class="wu-list-none wu-p-0 wu-m-0 wu-border-solid wu-border-0 wu-border-gray-200">

          <!-- Coupon -->
          <li class="wu-text-sm wu-text-gray-700 wu-border-solid wu-border-0 wu-border-b wu-border-gray-200 wu-m-0 wu-py-3 wu-px-4 wu-flex wu-items-center wu-justify-between">

            <span>

              <span class="wu-font-medium wu-text-gray-700 wu-block">

                Coupon Code

              </span>

              <span class="wu-text-sm wu-text-gray-600 wu-block">
                
                None applied.

              </span>

            </span>

            <div class="wu-ml-4 wu-flex-shrink-0 wu-flex">
              
              <a href="#" class="wu-no-underline">

                Add Coupon

              </a>

            </div>

          </li>
          <!-- Coupon End -->

        </ul>

      </div>

      <div>
        
        <!-- Title Element -->
        <div class="wu-p-4 wu-flex wu-items-center <?php echo wu_env_picker('', 'wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-b wu-border-gray-200'); ?>">

          <h3 class="wu-m-0 <?php echo wu_env_picker('', 'wu-widget-title'); ?>">

            <?php echo __('Additional Packages & Services', 'wp-ultimo'); ?>

          </h3>

        </div>
        <!-- Title Element - End -->

        <?php if ($membership->has_addons()) : ?>

          <ul class="wu-list-none wu-p-0 wu-m-0 wu-border-solid wu-border-0 wu-border-t wu-border-gray-200">

            <?php foreach ($membership->get_addons() as $addon) : ?>

            <!-- Packages and Services -->

            <li class="wu-text-sm wu-text-gray-700 wu-border-solid wu-border-0 wu-border-b wu-border-gray-200 wu-m-0 wu-py-3 wu-px-4 wu-flex wu-items-center wu-justify-between">

              <span>

                <span class="wu-font-medium wu-text-gray-700 wu-block">

                  <?php echo $addon->get_name(); ?>
                  <code class="wu-ml-2 wu-text-xs wu-font-normal">x 1</code>

                </span>

                <span class="wu-text-sm wu-text-gray-600 wu-block">
                  
                  <!-- <span class="wu-text-gray-500 wu-line-through">$29 per month</span>  -->
                  <?php echo $addon->get_price_description(); ?>

                </span>

              </span>

              <div class="wu-ml-4 wu-flex-shrink-0 wu-flex">

                <a 
                  title="<?php esc_attr_e('Product Details', 'wp-ultimo'); ?>"
                  href="<?php echo esc_attr(wu_get_form_url('see_product_details', array(
                    'product' => $addon->get_slug(),
                    'width'   => 500,
                  ))); ?>" 
                  class="wubox wu-ml-4 wu-no-underline"
                >

                  <?php _e('Details', 'wp-ultimo'); ?>

                </a>
                
                <?php if ($addon->is_recurring()) : ?>

                  <a href="#" class="wu-ml-4 wu-no-underline delete wu-text-red-500 hover:wu-text-red-600">

                    <?php _e('Cancel', 'wp-ultimo'); ?>

                  </a>
                
                <?php endif; ?>

              </div>

            </li>

            <!-- Packages and Services - End -->

            <?php endforeach; ?>

          </ul>
                
        <?php else : ?>      

          <div class="wu-px-4 wu-py-6 wu-text-center wu-text-gray-600">
            <?php _e('No packages or services found.', 'wp-ultimo'); ?>
          </div>

        <?php endif; ?>  

      </div>

      <?php if ($membership->is_recurring()) : ?>

        <!-- Summary Line - Total Applied -->
        <div class="<?php echo wu_env_picker('', 'wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-200'); ?> wu-m-0 wu-p-4 wu-rounded lg:wu-flex wu-items-center wu-justify-between">

          <div class="wu-text-lg">

            <small class="wu-block wu-text-xs wu-uppercase wu-font-bold wu-text-gray-600">
              <?php _e('Total', 'wp-ultimo'); ?>
            </small>
          
            <!-- <span class="wu-text-gray-500 wu-line-through">$29</span> -->
            
            <span>
              <?php echo wu_format_currency($membership->get_amount(), $membership->get_currency()) ?>
            </span>
            
            <span class="wu-text-gray-500 wu-text-sm">
              <?php echo $membership->get_recurring_description(); ?>
            </span>

          </div>

        </div>
        <!-- Summary Line - Total Applied End -->

      <?php endif; ?>

    <?php endif; ?>

  </div>

</div>
