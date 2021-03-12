<?php
/**
 * Checkbox multi field view.
 *
 * @since 2.0.0
 */
?>
<p>

  <label class="wu-block">

    <?php echo $field->title; ?>

  </label>

  <?php foreach ($field->options as $option_value => $option_name) : ?>

    <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>-<?php echo esc_attr($option_value); ?>">

      <input id="field-gateway-<?php echo esc_attr($option_value); ?>" type="checkbox" name="<?php echo esc_attr($field->id); ?>[]" value="<?php echo esc_attr($option_value); ?>" <?php echo $field->get_html_attributes(); ?> <?php checked($field->value == $option_value); ?>>

      <?php echo $option_name; ?>

    </label>

  <?php endforeach; ?>

  <span v-cloak class="wu-block wu-bg-red-100 wu-p-2 wu-mb-4" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
  </span>

</p>
