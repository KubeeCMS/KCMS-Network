<?php
/** global $plugins */
?>

<ul data-columns="1" class='items wu--mx-1 wu-overflow-hidden wu-multiselect-content wu-static wu-my-2'>

  <?php foreach ($plugins as $plugin_path => $plugin_data) : ?>
        
    <li class="item wu-box-border wu-m-0">
        
      <div class="wu-m-2 wu-bg-gray-100 wu-p-4 wu-border-gray-300 wu-border-solid wu-border wu-rounded">

        <div class="wu-items-center wu-justify-between">

          <div class="wu-block sm:wu-flex wu-items-center">
              
            <div class="wu-flex-1 wu-flex wu-flex-col wu-justify-between">
            
              <div>

                <span class="wu-font-bold wu-block wu-text-xs wu-uppercase wu-text-gray-700">
  
                  <?php echo $plugin_data['Name']; ?> 
  
                  <?php if (is_plugin_active_for_network($plugin_path)) : ?>
  
                    <span class="wu-text-xs wu-normal-case wu-font-normal wu-ml-2 wu-text-green-600">
                      <?php _e('Network Active', 'wp-ultimo'); ?>
                    </span>
  
                  <?php endif; ?>
                  
                </span>
                  
                <span class="wu-my-2 wu-block">
  
                  <?php echo strip_tags($plugin_data['Description']); ?>
  
                </span>
                
              </div>

              <div class="wu-block wu-mt-4">
                
                <span class="wu-text-xs wu-text-gray-700 wu-my-1 wu-mr-4 wu-block">
                  <?php printf(__('Version %s', 'wp-ultimo'), $plugin_data['Version']); ?>
                </span>

                <span class="wu-text-xs wu-text-gray-700 wu-my-1 wu-mr-4 wu-block">
                  <?php printf(__('by %s', 'wp-ultimo'), $plugin_data['Author']); ?>
                </span>

              </div>

            </div>

            <div class="sm:wu-ml-4 sm:wu-w-1/3 wu-mt-4 sm:wu-mt-0">

              <h3 class="wu-mb-1 wu-text-2xs wu-uppercase wu-text-gray-600">
              
                <?php _e('Visibility', 'wp-ultimo'); ?>

              </h3>

              <select name="modules[plugins][limit][<?php echo esc_attr($plugin_path); ?>][visibility]" class="wu-w-full">
                <option <?php selected($object->get_limitations()->plugins->{$plugin_path}->visibility === 'visible'); ?> value="visible"><?php _e('Visible', 'wp-ultimo'); ?></option>
                <option <?php selected($object->get_limitations()->plugins->{$plugin_path}->visibility === 'hidden'); ?> value="hidden"><?php _e('Hidden', 'wp-ultimo'); ?></option>
              </select>

              <h3 class="wu-my-1 wu-mt-4 wu-text-2xs wu-uppercase wu-text-gray-600">
              
                <?php _e('Behavior', 'wp-ultimo'); ?>

              </h3>

              <select name="modules[plugins][limit][<?php echo esc_attr($plugin_path); ?>][behavior]" class="wu-w-full">
                <option <?php selected($object->get_limitations()->plugins->{$plugin_path}->behavior === 'default'); ?> value="default"><?php _e('Default', 'wp-ultimo'); ?></option>
                <option <?php disabled(is_plugin_active_for_network($plugin_path)) ?> <?php selected($object->get_limitations()->plugins->{$plugin_path}->behavior === 'force_active'); ?> value="force_active"><?php _e('Force Activate', 'wp-ultimo'); ?></option>
                <option <?php disabled(is_plugin_active_for_network($plugin_path)) ?> <?php selected($object->get_limitations()->plugins->{$plugin_path}->behavior === 'force_inactive'); ?> value="force_inactive"><?php _e('Force Inactivate', 'wp-ultimo'); ?></option>
                <option <?php selected($object->get_limitations()->plugins->{$plugin_path}->behavior === 'force_active_locked'); ?> value="force_active_locked"><?php _e('Force Activate & Lock', 'wp-ultimo'); ?></option>
                <option <?php selected($object->get_limitations()->plugins->{$plugin_path}->behavior === 'force_inactive_locked'); ?> value="force_inactive_locked"><?php _e('Force Inactivate & Lock', 'wp-ultimo'); ?></option>
              </select>
            
            </div>

          </div>

        </div>

        <?php if ($object->model !== 'product' && $object->get_limitations(false)->plugins->exists($plugin_path)) : ?>

          <p class="wu-m-0 wu-mt-4 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded">
              <?php _e('This value is being applied only to this entity. Changes made to the membership or product permissions will not affect this particular value.', 'wp-ultimo'); ?>
          </p>

        <?php endif; ?>
      
      </div>
      
    </li>

  <?php endforeach; ?>

</ul>
