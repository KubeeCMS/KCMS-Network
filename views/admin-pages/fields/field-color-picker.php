<?php
/**
 * Color picker field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="wu-block">

    <?php

    /**
     * Adds the partial title template.
     * @since 2.0.0
     */
    wu_get_template('admin-pages/fields/partials/field-title', array(
      'field' => $field,
    ));

    ?>

    <div class="wu-mt-2">

      <color-picker class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="hidden" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?>></color-picker>

    </div>

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

</li>
