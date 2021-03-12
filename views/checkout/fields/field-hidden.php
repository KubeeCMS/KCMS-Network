<?php
/**
 * Hidden field view.
 *
 * @since 2.0.0
 */
?>
<?php if (is_array($field->value)) : ?>

  <?php foreach ($field->value as $index => $value) : ?>

    <input id="field-<?php echo esc_attr($field->id).'-'.esc_attr($index); ?>" name="<?php echo esc_attr($field->id); ?>[]" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($value); ?>" <?php echo $field->get_html_attributes(); ?>>

  <?php endforeach; ?>

<?php else : ?>

  <input id="field-<?php echo esc_attr($field->id); ?>" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?>>

<?php endif; ?>

<p v-cloak class="wu-block wu-bg-red-100 wu-p-2 wu-mb-4" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
</p>
