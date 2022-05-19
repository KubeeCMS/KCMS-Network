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

<?php

/**
 * Adds the partial error template.
 * @since 2.0.0
 */
wu_get_template('checkout/fields/partials/field-errors', array(
  'field' => $field,
));

?>
