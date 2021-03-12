<?php
/**
 * Text field view.
 *
 * @since 2.0.0
 */
?>
<p class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>">

    <?php echo $field->title; ?>

    <?php if ($field->required): ?>

      <span class="wu-text-red-500">*</span>

    <?php endif; ?>

    <?php echo wu_tooltip($field->tooltip); ?>

  </label>

  <input class="form-control wu-w-full wu-my-1 <?php echo esc_attr($field->classes); ?>" id="field-<?php echo esc_attr($field->id); ?>" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?>>

  <span v-cloak class="wu-block wu-bg-red-100 wu-p-2 wu-mb-4" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
  </span>

</p>
