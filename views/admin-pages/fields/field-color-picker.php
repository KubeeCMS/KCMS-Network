<?php
/**
 * Color picker field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <span class="wu-block">

    <?php if ($field->title) : ?>

      <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

        <?php echo $field->title; ?>

        <?php if ($field->tooltip) : ?>

          <?php echo wu_tooltip($field->tooltip); ?>

        <?php endif; ?>

      </h3>

    <?php endif; ?>

    <?php if ($field->desc) : ?>

      <span class="wu-my-1 wu-inline-block"><?php echo $field->desc; ?></span>

    <?php endif; ?>

    <div class="wu-mt-2">

      <color-picker class="form-control wu-w-full wu-my-1 wu_color_field" name="<?php echo esc_attr($field->id); ?>" type="hidden" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?>></color-picker>

    </div>
    
  </span>

</li>
