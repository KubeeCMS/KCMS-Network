<?php
/**
 * Image field view.
 *
 * @since 2.0.0
 */

/**
 * Set the media query.
 * 
 * When the stacked option is present
 * and set to true, ignore the flex arrangement
 * and make elements stacked.
 */
$mq = $field->stacked ? 'ignore-' : '';

?>

<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="<?php echo $mq; ?>md:wu-flex wu-items-center wu-w-full">

      <div class="<?php echo $mq; ?>md:wu-w-10/12">
    
      <?php

      /**
       * Adds the partial title template.
       * @since 2.0.0
       */
      wu_get_template('admin-pages/fields/partials/field-title', array(
        'field' => $field,
      ));

      ?>

      <div class="<?php echo $mq; ?>md:wu-w-9/12">
      
        <?php

        /**
         * Adds the partial title template.
         * @since 2.0.0
         */
        wu_get_template('admin-pages/fields/partials/field-description', array(
          'field' => $field,
        ));

        ?>

      </div>

    </div>

    <div class="wu-ml-0 <?php echo $mq; ?>md:wu-ml-4 <?php echo $mq; ?>md:wu-w-4/12 wu-mt-4 <?php echo $mq; ?>md:wu-mt-0">
      
      <div class="wu-wrapper-image-field wu-w-full wu-overflow-hidden">

        <div class="wu-relative wu-w-full wu-overflow-hidden">

          <div class="wu-self-center wu-rounded wu-flex <?php echo $mq; ?>md:wu-max-w-full wu-min-w-full <?php echo $mq; ?>md:wu-max-h-20 wu-overflow-hidden">
            
            <img 
              class="<?php echo $field->img ? '' : 'wu-absolute'; ?> wu-self-center wu-rounded sm:wu-max-w-full wu-min-w-full" 
              src="<?php echo $field->img; ?>"
            >

          </div>

          <div class="wu-wrapper-image-field-upload-actions wu-absolute wu-top-4 wu-right-4 <?php echo $mq; ?>md:wu-top-2 <?php echo $mq; ?>md:wu-right-2 wu-scale-150 <?php echo $mq; ?>md:wu-scale-100">

            <a title="<?php _e('Preview Image', 'wp-ultimo'); ?>" href="<?php echo $field->img; ?>" class="wubox wu-no-underline wu-text-center wu-inline-block wu-bg-black wu-opacity-60 wu-rounded-full wu-text-white wu-w-5 wu-h-5 wu-shadow-sm">

              <span class="dashicons-wu-eye1 wu-align-middle" style="top: -2px;"></span>

            </a>

            <a title="<?php _e('Remove Image', 'wp-ultimo'); ?>" href="#" class="wu-remove-image wu-no-underline wu-text-center wu-inline-block wu-bg-black wu-opacity-60 wu-rounded-full wu-text-white wu-w-5 wu-h-5 wu-shadow-sm">

              <span class="dashicons-wu-cross wu-align-middle"></span>

            </a>

          </div>

        </div>

        <input name="<?php echo esc_attr($field_slug); ?>" type="hidden" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?> />

        <div class="wu-add-image-wrapper <?php echo $mq; ?>md:wu-mt-0 wu-w-full" style="display: none;">

          <a class="button wu-w-full wu-text-center wu-add-image">
  
            <span class="dashicons-wu-upload"></span> <?php _e('Upload Image', 'wp-ultimo'); ?>
  
          </a>

        </div>

      </div>

    </div>

  </div>

</li>
