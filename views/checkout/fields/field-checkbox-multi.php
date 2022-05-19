<?php
/**
 * Checkbox multi field view.
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

  <?php foreach ($field->options as $option_value => $option_name) : ?>

    <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>-<?php echo esc_attr($option_value); ?>">

      <input id="field-gateway-<?php echo esc_attr($option_value); ?>" type="checkbox" name="<?php echo esc_attr($field->id); ?>[]" value="<?php echo esc_attr($option_value); ?>" <?php echo $field->get_html_attributes(); ?> <?php checked($field->value == $option_value); ?>>

      <?php echo $option_name; ?>

    </label>

  <?php endforeach; ?>

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
