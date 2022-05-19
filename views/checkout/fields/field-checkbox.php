<?php
/**
 * Checkbox field view.
 *
 * @since 2.0.0
 */
?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <label class="wu-block wu-my-4" for="field-<?php echo esc_attr($field->id); ?>">

    <input id="field-<?php echo esc_attr($field->id); ?>" type="checkbox" name="<?php echo esc_attr($field->id); ?>" value="1" <?php echo $field->get_html_attributes(); ?> <?php checked($field->value); ?>>

    <?php echo $field->title; ?>

    <?php echo wu_tooltip($field->tooltip); ?>

    <?php echo $field->desc; ?>

  </label>

  <?php

  /**
   * Adds the partial error template.
   * @since 2.0.0
   */
  wu_get_template('checkout/fields/partials/field-errors', array(
    'field' => $field,
  ));

  ?>

</div>
