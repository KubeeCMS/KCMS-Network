<?php
/**
 * Textarea field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <span class="wu-block wu-w-full">

    <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

      <?php echo $field->title; ?>

      <?php if ($field->tooltip) : ?>

        <?php echo wu_tooltip($field->tooltip); ?>

      <?php endif; ?>

    </h3>

    <textarea class="form-control wu-w-full wu-my-1 <?php echo esc_attr($field->classes); ?>" name="<?php echo esc_attr($field->id); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" <?php echo $field->get_html_attributes(); ?>><?php echo esc_attr($field->value); ?></textarea>

    <?php if ($field->desc) : ?>

      <p class="description" id="<?php echo $field->id; ?>-desc">

        <?php echo $field->desc; ?>

      </p>

    <?php endif; ?>

  </span>

</li>
