<?php
/** global $plugins */
?>

<ul data-columns="1" class='items wu--mx-1 wu-overflow-hidden wu-multiselect-content wu-static wu-my-2'>

  <?php foreach ($plugins as $plugin_path => $plugin_data) : ?>
        
    <li class="item wu-box-border wu-m-0">
        
      <div class="wu-m-1 wu-bg-gray-100 wu-p-3 wu-m-0 wu-border-gray-300 wu-border-solid wu-border wu-rounded">

        <div class="wu-items-center wu-flex wu-justify-between">

          <div class="wu-block wu-w-3/4">
              
            <div class="wu-block">
            
              <span class="wu-font-bold wu-block wu-text-xs wu-uppercase wu-text-gray-700">
                <?php echo $plugin_data['Name']; ?>
              </span>
                    
            </div> 
            
            <span class="wu-my-2 wu-block">

              <?php echo strip_tags($plugin_data['Description']); ?>

            </span>

            <div class="wu-block">
              
              <span class="wu-text-xs wu-mr-4">
                <?php printf(__('Version %s', 'wp-ultimo'), $plugin_data['Version']); ?>
              </span>

              <span class="wu-text-xs wu-mr-4">
                <?php printf(__('by %s', 'wp-ultimo'), $plugin_data['Author']); ?>
              </span>

              <?php if (is_plugin_active_for_network($plugin_path)) : ?>

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

            <select name="allowed_plugins[<?php echo esc_attr($plugin_path); ?>]" class="wu-w-full">
              <option <?php selected($object->get_limitations()->plugin_has_behavior($plugin_path, 'default')); ?> value="default"><?php _e('Keep as is', 'wp-ultimo'); ?></option>
              <option <?php selected($object->get_limitations()->plugin_has_behavior($plugin_path, 'activate')); ?> value="activate"><?php _e('Activate', 'wp-ultimo'); ?></option>
              <option <?php selected($object->get_limitations()->plugin_has_behavior($plugin_path, 'available')); ?> value="available"><?php _e('Make Available', 'wp-ultimo'); ?></option>
              <option <?php selected($object->get_limitations()->plugin_has_behavior($plugin_path, 'force_activation')); ?> value="force_activation"><?php _e('Force Activation', 'wp-ultimo'); ?></option>
              <option <?php selected($object->get_limitations()->plugin_has_behavior($plugin_path, 'force_deactivation')); ?> value="force_deactivation"><?php _e('Force Deactivation', 'wp-ultimo'); ?></option>
            </select>

          </div>

        </div>

        <?php if ($object->model!== 'product' && wu_get_isset($object->get_limitations(false)->get_allowed_plugins(), $plugin_path)) : ?>

          <p class="wu-m-0 wu-mt-4 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded">
              <?php _e('This value is being applied only to this entity. Changes made to the membership or product permissions will not affect this particular value.', 'wp-ultimo'); ?>
          </p>

        <?php endif; ?>
      
      </div>
      
    </li>

  <?php endforeach; ?>

</ul>
