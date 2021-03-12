<?php
/**
 * Checkbox field view.
 *
 * @since 2.0.0
 */
?>
<p class="wu-block">

  <label class="wu-block wu-my-4" for="field-<?php echo esc_attr($field->id); ?>">

    <input id="field-<?php echo esc_attr($field->id); ?>" type="checkbox" name="<?php echo esc_attr($field->id); ?>" value="1" <?php echo $field->get_html_attributes(); ?> <?php checked($field->value); ?>>

    <?php echo $field->title; ?>

  </label>

  <span v-cloak class="wu-block wu-bg-red-100 wu-p-2 wu-mb-4" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
  </span>

</p>
