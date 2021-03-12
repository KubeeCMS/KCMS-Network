<?php
/**
 * Toggle field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <span class="wu-block">

    <?php if ($field->title) : ?>

      <span class="wu-my-1 wu-text-xs wu-uppercase wu-font-bold wu-block">

        <?php echo $field->title; ?>

        <?php if ($field->tooltip) : ?>

          <?php echo wu_tooltip($field->tooltip); ?>

        <?php endif; ?>

      </span>

    <?php endif; ?>

    <?php if ($field->desc) : ?>

      <span class="wu-my-1 wu-inline-block wu-text-sm"><?php echo $field->desc; ?></span>

    <?php endif; ?>

  </span>

  <span class="wu-block wu-ml-2">

    <div class="wu-toggle">

      <input class="wu-tgl wu-tgl-ios" value="1" <?php checked($field->value == 1); ?>  id="wu-tg-<?php echo esc_attr($field->id); ?>" type="checkbox" name="<?php echo esc_attr($field_slug); ?>" <?php echo $field->get_html_attributes(); ?> />

      <label class="wu-tgl-btn wp-ui-highlight wu-bg-blue-500" for="wu-tg-<?php echo esc_attr($field->id); ?>"></label>

    </div>

  </span>

</li>
