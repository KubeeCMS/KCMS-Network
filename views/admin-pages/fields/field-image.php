<?php
/**
 * Image field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="wu-wrapper-image-field wu-flex wu-flex-col wu-w-full wu-overflow-hidden">

    <img class="<?php echo $field->img ? '' : 'wu-absolute'; ?> wu-self-center wu-mb-3" src="<?php echo $field->img; ?>" />

    <input name="<?php echo esc_attr($field_slug); ?>" type="hidden" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?> />

    <a class="button wu-w-full wu-text-center wu-add-image">

      <?php echo $field->title ? esc_html($field->title) : __('Set Image', 'wp-ultimo'); ?>

    </a>

    <a href="#" style="display: none;" class="wu-remove-image wu-no-underline button-link-delete wu-w-full wu-text-center wu-mt-3">

      <?php _e('Remove Image', 'wp-ultimo'); ?>

    </a>

  </div>

</li>
