<?php
/**
 * Color field view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-my-6">

  <div class="wu-flex">

    <div class="wu-w-1/3">

      <label for="<?php echo esc_attr($field->id); ?>">

        <?php echo $field->title; ?>

      </label>

    </div>

    <div class="wu-w-2/3">

      <input class="field-<?php echo esc_attr($field->id); ?>" name="<?php echo esc_attr($field->id); ?>" type="text" id="<?php echo esc_attr($field->id); ?>" class="regular-text" value="<?php echo wu_get_setting($field->id); ?>" placeholder="<?php echo $field->placeholder ? $field->placeholder : ''; ?>">

      <?php if ($field->desc) : ?>

        <p class="description" id="<?php echo $field->id; ?>-desc">

          <?php echo $field->desc; ?>

        </p>

      <?php endif; ?>

    </div>

  </div>

  <?php // if (isset($field['tooltip'])) {echo WU_Util::tooltip($field['tooltip']);} ?>

</div>

<script type="text/javascript">
(function($) {
    $(function() {
        // Add Color Picker to all inputs that have 'color-field' class
        $('.field-<?php echo esc_attr($field->id); ?>').wpColorPicker();
    });
})(jQuery);
</script>
