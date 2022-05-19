<?php
/** global $themes */
?>

<ul data-columns="1" class='items wu--mx-1 wu-overflow-hidden wu-multiselect-content wu-static wu-my-2'>

  <?php foreach ($themes as $theme_path => $theme_data) : ?>

    <?php

    $theme_settings = $object->get_limitations()->themes->{$theme_path};

    if ($theme_settings->behavior === 'force_active') {

      $section['state']['force_active_theme'] = $theme_path;

    } // end if;

    ?>
        
    <li class="item wu-box-border wu-m-0">
        
      <div class="wu-m-2 wu-bg-gray-100 wu-p-4 wu-border-gray-300 wu-border-solid wu-border wu-rounded">

        <div class="wu-flex wu-justify-between">

          <div class="wu-flex-1 wu-flex wu-flex-col wu-justify-between">
              
            <div class="wu-block">
            
              <span class="wu-font-bold wu-block wu-text-xs wu-uppercase wu-text-gray-700">

                <?php echo $theme_data['Name']; ?>

              </span>
                    
              <span class="wu-my-2 wu-block">
                
                <?php echo wp_trim_words(strip_tags($theme_data['Description']), 40); ?>
                
              </span>

            </div> 

            <div class="wu-block wu-mt-4">
              
              <span class="wu-text-xs wu-text-gray-700 wu-my-1 wu-mr-4 wu-block">
                <?php printf(__('Version %s', 'wp-ultimo'), $theme_data['Version']); ?>
              </span>

              <span class="wu-text-xs wu-text-gray-700 wu-my-1 wu-mr-4 wu-block">
                <?php printf(__('by %s', 'wp-ultimo'), $theme_data['Author']); ?>
              </span>

            </div>
          
          </div> 
          
          <div class="sm:wu-ml-4 sm:wu-w-1/3 wu-mt-4 sm:wu-mt-0">

            <img class="wu-rounded wu-w-full wu-image-preview" src="<?php echo esc_url($theme_data->get_screenshot()); ?>" data-image="<?php echo esc_url($theme_data->get_screenshot()); ?>">

            <h3 class="wu-mb-1 wu-text-2xs wu-uppercase wu-text-gray-600">
            
              <?php _e('Visibility', 'wp-ultimo'); ?>

            </h3>

            <select name="modules[themes][limit][<?php echo esc_attr($theme_path); ?>][visibility]" class="wu-w-full">
              <option <?php selected($theme_settings->visibility === 'visible'); ?> value="visible"><?php _e('Visible', 'wp-ultimo'); ?></option>
              <option <?php selected($theme_settings->visibility === 'hidden'); ?> value="hidden"><?php _e('Hidden', 'wp-ultimo'); ?></option>
            </select>

            <h3 class="wu-my-1 wu-mt-4 wu-text-2xs wu-uppercase wu-text-gray-600">
            
              <?php _e('Behavior', 'wp-ultimo'); ?>

            </h3>

            <select v-on:change="force_active_theme = ($event.target.value === 'force_active' ? '<?php echo esc_attr($theme_path); ?>' : '')" name="modules[themes][limit][<?php echo esc_attr($theme_path); ?>][behavior]" class="wu-w-full">
              <option <?php selected($theme_settings->behavior === 'available'); ?> value="available"><?php _e('Available', 'wp-ultimo'); ?></option>
              <option <?php selected($theme_settings->behavior === 'not_available'); ?> value="not_available"><?php _e('Not Available', 'wp-ultimo'); ?></option>
              <option :disabled="force_active_theme !== '' && force_active_theme != '<?php echo esc_attr($theme_path); ?>'" <?php selected($theme_settings->behavior === 'force_active'); ?> value="force_active"><?php _e('Force Activate', 'wp-ultimo'); ?></option>
            </select>
          
          </div>

        </div>

        <?php if ($object->model !== 'product' && $object->get_limitations(false)->themes->exists($theme_path)) : ?>

          <p class="wu-m-0 wu-mt-4 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded">
              <?php _e('This value is being applied only to this entity. Changes made to the membership or product permissions will not affect this particular value.', 'wp-ultimo'); ?>
          </p>

        <?php endif; ?>
      
      </div>
      
    </li>

  <?php endforeach; ?>

</ul>
