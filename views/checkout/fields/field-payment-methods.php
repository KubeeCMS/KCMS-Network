<?php
/**
 * Payment methods field view.
 *
 * @since 2.0.0
 */
?>
<p class="<?php echo esc_attr($field->wrapper_classes); ?>" v-cloak v-show="!(order && order.is_free)">

  <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>">

    <?php echo $field->title; ?>

  </label>

  <?php foreach (wu_get_active_gateway_as_options() as $option_value => $option_name) : ?>

    <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>-<?php echo esc_attr($option_value); ?>">

      <input id="field-gateway-<?php echo esc_attr($option_value); ?>" type="radio" name="gateway" value="<?php echo esc_attr($option_value); ?>" <?php echo $field->get_html_attributes(); ?> <?php checked($field->value == $option_value); ?> v-model="gateway">

		<?php echo $option_name; ?>

    </label>

  <?php endforeach; ?>

  <span v-cloak class="wu-block wu-bg-red-100 wu-p-2" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
  </span>

  <!-- <input class="form-control wu-w-full wu-my-1 <?php echo esc_attr($field->classes); ?>" id="field-<?php echo esc_attr($field->id); ?>" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?>>

  <span v-cloak class="wu-block wu-bg-red-100 wu-p-2" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
  </span> -->

  <?php
	do_action('wu_checkout_gateway_fields');
	?>

</p>
