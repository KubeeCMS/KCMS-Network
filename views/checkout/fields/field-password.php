<?php
/**
 * Password field view.
 *
 * @since 2.0.0
 */
?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <?php

  /**
   * Adds the partial title template.
   * @since 2.0.0
   */
  wu_get_template('checkout/fields/partials/field-title', array(
    'field' => $field,
  ));

  ?>

  <input class="form-control wu-w-full wu-my-1 <?php echo esc_attr(trim($field->classes)); ?>" id="field-<?php echo esc_attr($field->id); ?>" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?>>

  <?php if ($field->meter) : ?>

    <span class="wu-block">

      <span id="pass-strength-result" class="wu-py-2 wu-px-4 wu-bg-gray-100 wu-block wu-text-sm">

        <?php _e('Strength Meter', 'wp-ultimo'); ?>
        
      </span>

    </span>

  <?php endif; ?>

  <?php

  /**
   * Adds the partial error template.
   * @since 2.0.0
   */
  wu_get_template('checkout/fields/partials/field-errors', array(
    'field' => $field,
  ));

  ?>

  <!--
    <div class="wu-border-red-300"></div> 
    <div class="wu-border-yellow-300"></div> 
  -->

</div>
