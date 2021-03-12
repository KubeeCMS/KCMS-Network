<?php
/**
 * Product List
 *
 * @since 2.0.0
 */
?>
<div class="wu-widget-inset">

  <div class="">

    <?php if ($membership->get_all_products()) : ?>

      <?php foreach ($membership->get_all_products() as $line_item) : ?>

        <div class="wu-flex wu-items-center wu-p-4 wu-border-solid wu-border-0 wu-border-b wu-border-gray-300">

          <div class="wu-w-thumb wu-h-thumb">
        
            <?php if ($line_item['product']->get_featured_image()) : ?>
              
              <img 
                class="wu-w-thumb wu-h-thumb wu-rounded" 
                src="<?php echo esc_url($line_item['product']->get_featured_image()); ?>" 
                alt="<?php echo esc_attr($line_item['product']->get_name()); ?>"
              >

            <?php else : ?>

              <div class="wu-w-thumb wu-h-thumb wu-bg-gray-200 wu-rounded wu-text-gray-600 wu-flex wu-items-center wu-justify-center">
                <span class="dashicons-wu-image"></span>
              </div>

            <?php endif; ?>

          </div>

          <div class="wu-flex-1 wu-mx-4 wu-items-center">

            <span class="wu-text-sm wu-font-medium wu-block">
            
              <?php echo $line_item['product']->get_name(); ?>
            
              <span class="wu-ml-1 wu-text-xs wu-py-1 wu-px-2 wu-rounded wu-inline-block <?php echo esc_attr($line_item['product']->get_type_class()); ?>">
              
                <?php echo $line_item['product']->get_type_label(); ?>
                
              </span>

              <span class="wu-ml-1 wu-font-mono wu-text-xs wu-py-1 wu-px-2 wu-rounded wu-inline-block wu-bg-gray-200 wu-text-gray-700">
                x1
              </span>
            
            </span>

            <small class="wu-text-gray-600 wu-text-xs wu-block"><?php echo $line_item['product']->get_price_description(); ?></small>
          
          </div>

          <div class="wu-text-right wu-flex-1">
              
            <ul class="wu-ml-0">
            
              <?php if ($line_item['product']->get_type() !== 'plan') : ?>

                <li class="wu-inline-block wu-p-0 wu-m-0 wu-ml-4">
                  <a 
                    class="wu-no-underline wu-text-red-600 wubox" 
                    title="<?php esc_attr_e('Remove Product', 'wp-ultimo'); ?>"
                    href="<?php echo esc_attr(wu_get_form_url('remove_membership_product', array('id' => $membership->get_id(), 'product_id' => $line_item['product']->get_id()))); ?>"
                  >
                    <?php _e('Remove', 'wp-ultimo'); ?>
                  </a>
                </li>

              <?php else : ?>

                <li class="wu-inline-block wu-p-0 wu-m-0 wu-ml-4">
                  <a 
                    class="wu-no-underline wubox" 
                    title="<?php esc_attr_e('Change Membership Plan', 'wp-ultimo'); ?>"
                    href="<?php echo esc_attr(wu_get_form_url('change_membership_plan', array('id' => $membership->get_id(), 'product_id' => $line_item['product']->get_id()))); ?>"
                  >
                    <?php _e('Change', 'wp-ultimo'); ?>
                  </a>
                </li>

              <?php endif; ?>

            </ul>

          </div>

        </div>

      <?php endforeach; ?>

    <?php else : ?>

      <div class="wu-p-4">

        <?php _e('Products not found.', 'wp-ultimo'); ?>

      </div>

    <?php endif; ?>

  </div>

  <div class="wu-flex wu-p-4 wu-bg-gray-200">
    
    <div class="wu-ml-auto">
      
      <a 
        class="button wu-ml-2 wubox"
        title="<?php esc_attr_e('Add new Product', 'wp-ultimo'); ?>"
        href="<?php echo esc_attr(wu_get_form_url('edit_membership_product', array('id' => $membership->get_id()))); ?>"
      >
        <span class="dashicons-wu-circle-with-plus wu-align-text-bottom"></span>
        <?php _e('Add new Product', 'wp-ultimo'); ?>
      </a>
    
    </div>

  </div>

</div>
