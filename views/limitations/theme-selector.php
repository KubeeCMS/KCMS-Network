<?php
/** global $themes */
?>

<ul data-columns="1" class='items wu--mx-1 wu-overflow-hidden wu-multiselect-content wu-static wu-my-2'>

  <?php foreach ($themes as $theme_path => $theme_data) : ?>
        
    <li class="item wu-box-border wu-m-0">
        
      <div class="wu-m-1 wu-bg-gray-100 wu-p-3 wu-m-0 wu-border-gray-300 wu-border-solid wu-border wu-rounded">

        <div class="wu-items-center wu-flex wu-justify-between">

          <div class="wu-hidden md:wu-block wu-w-1/4 wu-mr-4">

            <img class="wu-rounded wu-w-full" src="<?php echo esc_url($theme_data->get_screenshot()); ?>">
            
          </div>

          <div class="wu-block wu-w-3/4">
              
            <div class="wu-block">
            
              <span class="wu-font-bold wu-block wu-text-xs wu-uppercase wu-text-gray-700">
                <?php echo $theme_data['Name']; ?>
              </span>
                    
            </div> 
            
            <span class="wu-my-2 wu-block">

              <?php echo wp_trim_words(strip_tags($theme_data['Description']), 20); ?>

            </span>

            <div class="wu-block">
              
              <span class="wu-text-xs wu-mr-4">
                <?php printf(__('Version %s', 'wp-ultimo'), $theme_data['Version']); ?>
              </span>

              <span class="wu-text-xs wu-mr-4">
                <?php printf(__('by %s', 'wp-ultimo'), $theme_data['Author']); ?>
              </span>

              <?php if (is_plugin_active_for_network($theme_path)) : ?>

                <span class="wu-text-xs wu-mr-4 wu-text-green-600">
                  <?php _e('Network Active', 'wp-ultimo'); ?>
                </span>

              <?php endif; ?>

            </div>
          
          </div> 
          
          <div class="wu-block wu-ml-4 wu-w-1/4">

            <h3 class="wu-my-1 wu-text-2xs wu-uppercase wu-text-gray-600">
            
              <?php _e('Behavior', 'wp-ultimo'); ?>

            </h3>

            <select name="allowed_themes[<?php echo esc_attr($theme_path); ?>]" class="wu-w-full">
              <option <?php selected($object->get_limitations()->theme_has_behavior($theme_path, 'default')); ?> value="default"><?php _e('Keep as is', 'wp-ultimo'); ?></option>
              <option <?php selected($object->get_limitations()->theme_has_behavior($theme_path, 'activate')); ?> value="activate"><?php _e('Activate', 'wp-ultimo'); ?></option>
              <option <?php selected($object->get_limitations()->theme_has_behavior($theme_path, 'available')); ?> value="available"><?php _e('Make Available', 'wp-ultimo'); ?></option>
              <option <?php selected($object->get_limitations()->theme_has_behavior($theme_path, 'hide')); ?> value="hide"><?php _e('Hide', 'wp-ultimo'); ?></option>
            </select>

          </div>

        </div>

        <?php if (wu_get_isset($object->get_limitations(false)->get_allowed_themes(), $theme_path)) : ?>

          <p class="wu-m-0 wu-mt-4 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded">
              <?php _e('This value is being applied only to this entity. Changes made to the membership or product permissions will not affect this particular value.', 'wp-ultimo'); ?>
          </p>

        <?php endif; ?>
      
      </div>
      
    </li>

  <?php endforeach; ?>

</ul>
