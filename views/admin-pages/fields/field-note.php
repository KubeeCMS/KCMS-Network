<?php
/**
 * Note field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <?php if ($field->title) : ?>

  <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

    <?php echo $field->title; ?>

    <?php if ($field->tooltip) : ?>

      <?php echo wu_tooltip($field->tooltip); ?>

    <?php endif; ?>

  </h3>

  <?php endif; ?>

  <p class="wu-my-0 <?php echo esc_attr($field->classes); ?>">
    <?php echo $field->desc; ?>
  </p>

</li>
