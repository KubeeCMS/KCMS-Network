<?php
/**
 * Select field view.
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

  <select
    class="form-control wu-w-full wu-my-1 <?php echo esc_attr(trim($field->classes)); ?>"
    id="field-<?php echo esc_attr($field->id); ?>"
    name="<?php echo esc_attr($field->id); ?>"
    value="<?php echo esc_attr($field->value); ?>"
    <?php echo $field->get_html_attributes(); ?>
  >

  <?php if ($field->placeholder) : ?>
    
    <option <?php checked(!$field->value); ?> class="wu-opacity-75"><?php echo $field->placeholder; ?></option>
    
    <?php endif; ?>
    
    <?php foreach ($field->options as $key => $label) : ?>
      
      <option
      value="<?php echo esc_attr($key); ?>"
      <?php checked($key, $field->value); ?>
      >
      <?php echo $label; ?>
    </option>
    
    <?php endforeach; ?>
    
    <?php if ($field->options_template) : ?>

      <?php echo $field->options_template; ?>

    <?php endif; ?>

  </select>

  <?php

  /**
   * Adds the partial title template.
   * @since 2.0.0
   */
  wu_get_template('checkout/fields/partials/field-errors', array(
    'field' => $field,
  ));

  ?>

</div>
