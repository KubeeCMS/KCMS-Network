<?php
/**
 * Title field partial view.
 *
 * @since 2.0.0
 */
?>

<?php if ($field->title) : ?>

  <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>">
    
    <?php echo $field->title; ?>

    <?php if ($field->required) : ?>

      <span class="wu-checkout-required-field wu-text-red-500">*</span>

    <?php endif; ?>

    <?php echo wu_tooltip($field->tooltip); ?>

  </label>

<?php endif; ?>
