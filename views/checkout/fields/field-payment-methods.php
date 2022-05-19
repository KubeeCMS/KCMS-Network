<?php
/**
 * Payment methods field view.
 *
 * @since 2.0.0
 */

$active_gateways = wu_get_active_gateway_as_options();

?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" v-cloak v-show="order && order.should_collect_payment" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <?php

  /**
   * Adds the partial title template.
   * @since 2.0.0
   */
  wu_get_template('checkout/fields/partials/field-title', array(
    'field' => $field,
  ));

  ?>

  <?php foreach ($active_gateways as $option_value => $option_name) : ?>

    <?php if (count($active_gateways) === 1) : ?>

      <input 
        id="field-gateway" 
        type="hidden" 
        name="gateway" 
        value="<?php echo esc_attr($option_value); ?>"
        v-model="gateway"
        <?php echo $field->get_html_attributes(); ?>
      >

    <?php else : ?>

      <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>-<?php echo esc_attr($option_value); ?>">

        <input 
          id="field-gateway-<?php echo esc_attr($option_value); ?>" 
          type="radio" 
          name="gateway" 
          value="<?php echo esc_attr($option_value); ?>"
          v-model="gateway"
          class="<?php echo trim($field->classes); ?>"
          <?php echo $field->get_html_attributes(); ?>
          <?php checked($field->value == $option_value); ?> 
        >

        <?php echo $option_name; ?>

      </label>

    <?php endif; ?>

  <?php endforeach; ?>

  <?php

  /**
   * Adds the partial error template.
   * @since 2.0.0
   */
  wu_get_template('checkout/fields/partials/field-errors', array(
    'field' => $field,
  ));

  /**
   * Load Gateway fields
   * @since 2.0.0
   */
  do_action('wu_checkout_gateway_fields');

  ?>

</div>
