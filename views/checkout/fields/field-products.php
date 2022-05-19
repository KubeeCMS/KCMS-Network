<?php
/**
 * Products field view.
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

  <?php foreach (wu_get_plans() as $option) : ?>

    <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>-<?php echo esc_attr($option->get_id()); ?>">

      <input id="field-products-<?php echo esc_attr($option->get_id()); ?>" type="checkbox" name="products[]" value="<?php echo esc_attr($option->get_id()); ?>" <?php echo $field->get_html_attributes(); ?> <?php checked($field->value == $option->get_id()); ?> v-model="products">

      <?php echo $option->get_name(); ?>

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
